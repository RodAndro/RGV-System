<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mobile_recent_request_dismissals')) {
            Schema::create('mobile_recent_request_dismissals', function (Blueprint $table) {
                $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('borrow_request_id')->constrained('borrow_requests')->cascadeOnDelete();
                $table->timestampTz('dismissed_at')->useCurrent();
                $table->primary(['employee_id', 'borrow_request_id']);
            });
        }

        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE public.mobile_recent_request_dismissals ENABLE ROW LEVEL SECURITY');
        DB::statement('REVOKE ALL ON public.mobile_recent_request_dismissals FROM anon, authenticated');

        DB::unprepared(<<<'SQL'
            create or replace function public.mobile_dashboard()
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
                select jsonb_build_object(
                    'summary', jsonb_build_object(
                        'total', count(*),
                        'pending', count(*) filter (where recent.status = 'pending'),
                        'approved', count(*) filter (where recent.status = 'approved'),
                        'borrowed', count(*) filter (where recent.status = 'borrowed'),
                        'returned', count(*) filter (where recent.status = 'returned'),
                        'overdue', count(*) filter (where recent.status = 'borrowed' and recent.due_date < current_date)
                    ),
                    'recent_requests', coalesce(jsonb_agg(jsonb_build_object(
                        'id', recent.id, 'request_number', recent.request_number, 'status', recent.status,
                        'borrow_date', recent.borrow_date, 'due_date', recent.due_date,
                        'items', coalesce((
                            select jsonb_agg(inventory.name order by item.id)
                            from public.borrow_items as item
                            left join public.inventories as inventory on inventory.id = item.inventory_id
                            where item.borrow_request_id = recent.id
                        ), '[]'::jsonb)
                    ) order by recent.created_at desc) filter (
                        where recent.id is not null
                          and not exists (
                              select 1
                              from public.mobile_recent_request_dismissals as dismissed
                              where dismissed.employee_id = v_employee_id
                                and dismissed.borrow_request_id = recent.id
                          )
                    ), '[]'::jsonb)
                ) into v_result
                from (
                    select * from public.borrow_requests
                    where employee_id = v_employee_id and deleted_at is null
                    order by created_at desc
                ) as recent;
                return v_result;
            end;
            $$;

            create or replace function public.mobile_dismiss_recent_request(p_request_id bigint)
            returns jsonb
            language plpgsql
            security definer
            set search_path = public
            as $$
            declare
                v_employee_id bigint;
            begin
                v_employee_id := public.mobile_assert_access();
                if not exists (
                    select 1
                    from public.borrow_requests as request
                    where request.id = p_request_id
                      and request.employee_id = v_employee_id
                      and request.deleted_at is null
                ) then
                    raise exception 'Borrow request not found.' using errcode = 'P0002';
                end if;

                insert into public.mobile_recent_request_dismissals (
                    employee_id, borrow_request_id, dismissed_at
                ) values (
                    v_employee_id, p_request_id, now()
                )
                on conflict (employee_id, borrow_request_id)
                do update set dismissed_at = excluded.dismissed_at;

                return jsonb_build_object('dismissed', true, 'request_id', p_request_id);
            end;
            $$;

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
                    'request_id', request.id, 'borrow_date', request.borrow_date, 'due_date', request.due_date,
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
                  and request.status in ('approved', 'borrowed')
                  and request.deleted_at is null
                  and coalesce(item.is_returned, false) = false;
                return v_result;
            end;
            $$;

            revoke execute on function public.mobile_dismiss_recent_request(bigint) from public, anon;
            grant execute on function public.mobile_dismiss_recent_request(bigint) to authenticated;
            notify pgrst, 'reload schema';
            SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
                drop function if exists public.mobile_dismiss_recent_request(bigint);

                create or replace function public.mobile_dashboard()
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
                    select jsonb_build_object(
                        'summary', jsonb_build_object(
                            'total', count(*),
                            'pending', count(*) filter (where recent.status = 'pending'),
                            'approved', count(*) filter (where recent.status = 'approved'),
                            'borrowed', count(*) filter (where recent.status = 'borrowed'),
                            'returned', count(*) filter (where recent.status = 'returned'),
                            'overdue', count(*) filter (where recent.status = 'borrowed' and recent.due_date < current_date)
                        ),
                        'recent_requests', coalesce(jsonb_agg(jsonb_build_object(
                            'id', recent.id, 'request_number', recent.request_number, 'status', recent.status,
                            'borrow_date', recent.borrow_date, 'due_date', recent.due_date,
                            'items', coalesce((
                                select jsonb_agg(inventory.name order by item.id)
                                from public.borrow_items as item
                                left join public.inventories as inventory on inventory.id = item.inventory_id
                                where item.borrow_request_id = recent.id
                            ), '[]'::jsonb)
                        ) order by recent.created_at desc) filter (where recent.id is not null), '[]'::jsonb)
                    ) into v_result
                    from (
                        select * from public.borrow_requests
                        where employee_id = v_employee_id and deleted_at is null
                        order by created_at desc
                    ) as recent;
                    return v_result;
                end;
                $$;

                notify pgrst, 'reload schema';
                SQL);
        }

        Schema::dropIfExists('mobile_recent_request_dismissals');
    }
};
