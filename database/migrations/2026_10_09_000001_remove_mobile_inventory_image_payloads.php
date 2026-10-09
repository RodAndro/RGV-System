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
            create or replace function public.mobile_inventory_lookup(p_code text)
            returns jsonb
            language plpgsql
            security definer
            set search_path = public
            as $$
            declare
                v_item jsonb;
            begin
                perform public.mobile_assert_access();
                select jsonb_build_object(
                    'id', inventory.id, 'item_code', inventory.item_code, 'name', inventory.name,
                    'brand', inventory.brand, 'description', inventory.description,
                    'category', category.name, 'unit', inventory.unit, 'quantity', inventory.quantity,
                    'condition', inventory.condition,
                    'location', inventory.location, 'status', inventory.status,
                    'is_active', inventory.is_active,
                    'is_available', inventory.status = 'available' and inventory.quantity > 0
                ) into v_item
                from public.inventories as inventory
                left join public.inventory_categories as category on category.id = inventory.category_id
                where inventory.is_active = true and inventory.deleted_at is null
                  and (upper(inventory.item_code) = upper(trim(p_code))
                       or inventory.qr_token = trim(p_code)
                       or inventory.id::text = trim(p_code))
                limit 1;

                if v_item is null then
                    raise exception 'Inventory item not found.' using errcode = 'P0002';
                end if;
                return v_item;
            end;
            $$;

            create or replace function public.mobile_inventory_search(p_query text default '', p_limit integer default 50)
            returns jsonb
            language plpgsql
            security definer
            set search_path = public
            as $$
            declare
                v_employee_id bigint;
                v_query text := trim(coalesce(p_query, ''));
                v_limit integer := greatest(1, least(coalesce(p_limit, 50), 100));
                v_items jsonb;
            begin
                v_employee_id := public.mobile_assert_access();
                if length(v_query) > 100 then
                    raise exception 'Search text must be 100 characters or fewer.' using errcode = '22023';
                end if;

                select coalesce(jsonb_agg(jsonb_build_object(
                    'id', results.id, 'item_code', results.item_code, 'name', results.name,
                    'brand', results.brand, 'description', results.description,
                    'category', results.category, 'quantity', results.quantity,
                    'unit', results.unit, 'status', results.status,
                    'condition', results.condition,
                    'is_active', results.is_active,
                    'is_available', results.status = 'available' and results.quantity > 0
                ) order by results.name), '[]'::jsonb)
                into v_items
                from (
                    select inventory.id, inventory.item_code, inventory.name, inventory.brand,
                           inventory.description, category.name as category, inventory.quantity,
                           inventory.unit, inventory.status, inventory.condition,
                           inventory.is_active
                    from public.inventories as inventory
                    left join public.inventory_categories as category on category.id = inventory.category_id
                    where inventory.is_active = true and inventory.deleted_at is null
                      and (v_query = '' or inventory.name ilike '%' || v_query || '%'
                           or inventory.item_code ilike '%' || v_query || '%'
                           or coalesce(inventory.brand, '') ilike '%' || v_query || '%'
                           or coalesce(category.name, '') ilike '%' || v_query || '%')
                    order by inventory.name
                    limit v_limit
                ) as results;

                return v_items;
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
                where request.employee_id = v_employee_id and request.status = 'borrowed'
                  and request.deleted_at is null and item.is_returned = false;
                return v_result;
            end;
            $$;
            SQL);
    }
};