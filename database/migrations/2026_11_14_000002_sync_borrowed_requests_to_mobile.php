<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            create or replace function public.mobile_returnable_items()
            returns jsonb
            language plpgsql
            security definer
            set search_path = public
            as $$
            declare
                v_employee_id bigint;
                v_result jsonb;
            begin
                v_employee_id := public.mobile_assert_access();
                select coalesce(jsonb_agg(jsonb_build_object(
                    'borrow_item_id', item.id, 'request_number', request.request_number,
                    'request_id', request.id, 'status', request.status,
                    'borrow_date', request.borrow_date, 'due_date', request.due_date,
                    'quantity', item.quantity, 'condition_borrowed', item.condition_borrowed,
                    'inventory', jsonb_build_object(
                        'id', inventory.id, 'item_code', inventory.item_code, 'name', inventory.name,
                        'brand', inventory.brand, 'unit', inventory.unit,
                        'location', inventory.location
                    )
                ) order by request.created_at desc, item.id), '[]'::jsonb)
                into v_result
                from public.borrow_requests as request
                join public.borrow_items as item on item.borrow_request_id = request.id
                left join public.inventories as inventory on inventory.id = item.inventory_id
                where request.employee_id = v_employee_id
                  and request.status = 'borrowed'
                  and request.deleted_at is null
                  and coalesce(item.is_returned, false) = false;
                return v_result;
            end;
            $$;

            create or replace function public.mobile_return_borrow_items(
                p_request_id bigint, p_idempotency_key text, p_return_location text, p_items jsonb
            )
            returns jsonb
            language plpgsql
            security definer
            set search_path = public
            as $$
            declare
                v_employee_id bigint;
                v_request public.borrow_requests%rowtype;
                v_item record;
                v_borrow_item public.borrow_items%rowtype;
                v_restore integer;
                v_previous_payload jsonb;
                v_request_payload jsonb;
                v_cached_response jsonb;
            begin
                v_employee_id := public.mobile_assert_access();
                if p_idempotency_key is null or length(trim(p_idempotency_key)) = 0 or length(p_idempotency_key) > 64
                   or p_return_location is null or length(trim(p_return_location)) = 0
                   or length(p_return_location) > 255 or p_items is null
                   or jsonb_typeof(p_items) <> 'array' or jsonb_array_length(p_items) = 0 then
                    raise exception 'Return details are incomplete.' using errcode = '22023';
                end if;

                select * into v_request from public.borrow_requests
                where id = p_request_id and employee_id = v_employee_id and deleted_at is null for update;
                if not found or v_request.status not in ('borrowed', 'returned') then
                    raise exception 'Only borrowed requests can be returned.' using errcode = 'P0001';
                end if;

                v_request_payload := jsonb_build_object(
                    'return_location', trim(p_return_location), 'items', p_items
                );
                select response_payload, request_payload
                into v_cached_response, v_previous_payload
                from public.mobile_return_idempotency
                where employee_id = v_employee_id and borrow_request_id = p_request_id
                  and idempotency_key = trim(p_idempotency_key);
                if found then
                    if v_previous_payload <> v_request_payload then
                        raise exception 'This idempotency key was already used for a different return.' using errcode = '22023';
                    end if;
                    return v_cached_response;
                end if;
                if v_request.status = 'returned' then
                    v_cached_response := public.mobile_request_payload(p_request_id);
                    insert into public.mobile_return_idempotency (
                        employee_id, borrow_request_id, idempotency_key,
                        request_payload, response_payload
                    ) values (
                        v_employee_id, p_request_id, trim(p_idempotency_key),
                        v_request_payload, v_cached_response
                    );
                    return v_cached_response;
                end if;

                for v_item in
                    select * from jsonb_to_recordset(p_items) as item(
                        borrow_item_id bigint, condition_returned text, damage_notes text
                    ) order by borrow_item_id
                loop
                    if v_item.condition_returned is null
                       or v_item.condition_returned not in ('new', 'good', 'fair', 'poor', 'damaged')
                       or length(coalesce(v_item.damage_notes, '')) > 1000 then
                        raise exception 'Invalid return condition or damage notes.' using errcode = '22023';
                    end if;
                    select * into v_borrow_item from public.borrow_items
                    where id = v_item.borrow_item_id and borrow_request_id = p_request_id for update;
                    if not found then
                        raise exception 'A selected item does not belong to this request.' using errcode = '22023';
                    end if;
                    if not coalesce(v_borrow_item.is_returned, false) then
                        v_restore := case when v_borrow_item.reserved_quantity > 0
                            then v_borrow_item.reserved_quantity else v_borrow_item.quantity end;
                        update public.borrow_items
                        set condition_returned = v_item.condition_returned,
                            damage_notes = v_item.damage_notes, is_returned = true,
                            returned_at = now(), reserved_quantity = 0, updated_at = now()
                        where id = v_borrow_item.id;
                        update public.inventories set quantity = quantity + v_restore, updated_at = now()
                        where id = v_borrow_item.inventory_id;
                    end if;
                end loop;

                update public.borrow_requests
                set return_location = trim(p_return_location), updated_at = now()
                where id = p_request_id;
                if not exists (
                    select 1 from public.borrow_items
                    where borrow_request_id = p_request_id
                      and coalesce(is_returned, false) = false
                ) then
                    update public.borrow_requests
                    set status = 'returned', return_date = current_date,
                        returned_at = now(), updated_at = now()
                    where id = p_request_id;
                end if;

                v_cached_response := public.mobile_request_payload(p_request_id);
                insert into public.mobile_return_idempotency (
                    employee_id, borrow_request_id, idempotency_key,
                    request_payload, response_payload
                ) values (
                    v_employee_id, p_request_id, trim(p_idempotency_key),
                    v_request_payload, v_cached_response
                );
                return v_cached_response;
            end;
            $$;

            drop policy if exists mobile_employee_realtime_own_borrow_requests
                on public.borrow_requests;
            alter table public.borrow_requests enable row level security;
            create policy mobile_employee_realtime_own_borrow_requests
                on public.borrow_requests
                for select to authenticated
                using (employee_id = public.mobile_assert_access());

            grant execute on function public.mobile_employee_id() to authenticated;
            revoke execute on function public.mobile_assert_access() from public, anon;
            grant execute on function public.mobile_assert_access() to authenticated;
            grant select on public.borrow_requests to authenticated;
            revoke execute on function public.mobile_returnable_items() from public, anon;
            grant execute on function public.mobile_returnable_items() to authenticated;
            revoke execute on function public.mobile_return_borrow_items(bigint, text, text, jsonb)
                from public, anon;
            grant execute on function public.mobile_return_borrow_items(bigint, text, text, jsonb)
                to authenticated;

            do $$
            begin
                if exists (select 1 from pg_publication where pubname = 'supabase_realtime')
                   and not exists (
                       select 1 from pg_publication_tables
                       where pubname = 'supabase_realtime'
                         and schemaname = 'public'
                         and tablename = 'borrow_requests'
                   ) then
                    execute 'alter publication supabase_realtime add table public.borrow_requests';
                end if;
            end;
            $$;

            notify pgrst, 'reload schema';
            SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            drop policy if exists mobile_employee_realtime_own_borrow_requests
                on public.borrow_requests;
            revoke select on public.borrow_requests from authenticated;
            revoke execute on function public.mobile_employee_id() from authenticated;
            revoke execute on function public.mobile_assert_access() from authenticated;
            notify pgrst, 'reload schema';
            SQL);
    }
};
