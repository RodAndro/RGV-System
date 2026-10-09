create extension if not exists pgcrypto with schema extensions;

create table if not exists public.mobile_return_idempotency (
    employee_id bigint not null references public.users(id) on delete cascade,
    borrow_request_id bigint not null references public.borrow_requests(id) on delete cascade,
    idempotency_key text not null,
    request_payload jsonb not null,
    response_payload jsonb not null,
    created_at timestamptz not null default now(),
    primary key (employee_id, borrow_request_id, idempotency_key)
);

alter table public.mobile_return_idempotency enable row level security;
revoke all on public.mobile_return_idempotency from anon, authenticated;

create or replace function public.link_mobile_employee()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
    if tg_op = 'DELETE' then
        update public.users set supabase_auth_id = null where supabase_auth_id = old.id;
        return old;
    end if;

    if tg_op = 'UPDATE'
       and new.email is not distinct from old.email
       and new.email_confirmed_at is not distinct from old.email_confirmed_at
       and new.encrypted_password is not distinct from old.encrypted_password then
        return new;
    end if;

    update public.users set supabase_auth_id = null
    where supabase_auth_id = new.id and lower(email) <> lower(coalesce(new.email, ''));

    if new.email_confirmed_at is not null and new.email is not null then
        update public.users as employee
        set supabase_auth_id = new.id,
            password = coalesce(nullif(new.encrypted_password, ''), employee.password)
        where lower(employee.email) = lower(new.email)
          and employee.is_active = true
          and (employee.supabase_auth_id is null or employee.supabase_auth_id = new.id)
          and exists (
              select 1 from public.model_has_roles as assignment
              join public.roles as role on role.id = assignment.role_id
              where assignment.model_id = employee.id
                and assignment.model_type = 'App\Models\User'
                and role.name = 'employee' and role.guard_name = 'web'
          );
    end if;

    return new;
end;
$$;

drop trigger if exists link_mobile_employee_auth_user on auth.users;
create trigger link_mobile_employee_auth_user
after insert or update or delete on auth.users
for each row execute function public.link_mobile_employee();

create or replace function public.sync_employee_password_to_auth()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
    if new.password is distinct from old.password and new.supabase_auth_id is not null then
        update auth.users
                set encrypted_password = case
                                when left(new.password, 4) = '$2y$' then '$2a$' || substr(new.password, 5)
                                else new.password
                        end,
                        updated_at = now()
        where id = new.supabase_auth_id
                    and encrypted_password is distinct from case
                                when left(new.password, 4) = '$2y$' then '$2a$' || substr(new.password, 5)
                                else new.password
                        end;
    end if;
    return new;
end;
$$;

drop trigger if exists sync_employee_password_to_auth_user on public.users;
create trigger sync_employee_password_to_auth_user
after update of password on public.users
for each row execute function public.sync_employee_password_to_auth();

update public.users as employee
set supabase_auth_id = auth_user.id,
    password = coalesce(nullif(auth_user.encrypted_password, ''), employee.password)
from auth.users as auth_user
where lower(employee.email) = lower(auth_user.email)
  and auth_user.email_confirmed_at is not null
  and employee.is_active = true
  and employee.supabase_auth_id is null
  and exists (
      select 1 from public.model_has_roles as assignment
      join public.roles as role on role.id = assignment.role_id
      where assignment.model_id = employee.id
        and assignment.model_type = 'App\Models\User'
        and role.name = 'employee' and role.guard_name = 'web'
  );

create or replace function public.mobile_employee_id()
returns bigint
language plpgsql
security definer
set search_path = public
as $$
declare
    v_employee_id bigint;
begin
    select employee.id into v_employee_id
    from public.users as employee
    where employee.supabase_auth_id = auth.uid()
      and employee.is_active = true
      and exists (
          select 1 from public.model_has_roles as assignment
          join public.roles as role on role.id = assignment.role_id
          where assignment.model_id = employee.id
            and assignment.model_type = 'App\Models\User'
            and role.name = 'employee' and role.guard_name = 'web'
      );

    if v_employee_id is null then
        raise exception 'This account is not linked to an active employee.' using errcode = '42501';
    end if;

    return v_employee_id;
end;
$$;

create or replace function public.mobile_assert_access()
returns bigint
language plpgsql
security definer
set search_path = public
as $$
declare
    v_employee_id bigint;
    v_requires_mfa boolean;
    v_has_auth_factor boolean;
begin
    v_employee_id := public.mobile_employee_id();
    select employee.mfa_enabled into v_requires_mfa
    from public.users as employee where employee.id = v_employee_id;

    select exists (
        select 1 from auth.mfa_factors as factor
        where factor.user_id = auth.uid() and factor.status = 'verified'
    ) into v_has_auth_factor;

    if (coalesce(v_requires_mfa, false) or v_has_auth_factor)
       and coalesce(auth.jwt() ->> 'aal', 'aal1') <> 'aal2' then
        raise exception 'Additional authentication is required.' using errcode = '42501';
    end if;

    return v_employee_id;
end;
$$;

create or replace function public.mobile_employee_profile()
returns jsonb
language plpgsql
security definer
set search_path = public
as $$
declare
    v_employee_id bigint;
    v_profile jsonb;
    v_has_auth_factor boolean;
begin
    v_employee_id := public.mobile_employee_id();
    select exists (
        select 1 from auth.mfa_factors as factor
        where factor.user_id = auth.uid() and factor.status = 'verified'
    ) into v_has_auth_factor;

    select jsonb_build_object(
        'id', employee.id, 'name', employee.name, 'email', employee.email,
        'phone', employee.phone, 'avatar_path', employee.avatar_path,
        'is_active', employee.is_active,
        'mfa_enabled', employee.mfa_enabled or v_has_auth_factor,
        'role', 'employee'
    ) into v_profile
    from public.users as employee where employee.id = v_employee_id;

    return v_profile;
end;
$$;

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

create or replace function public.mobile_request_payload(p_request_id bigint)
returns jsonb
language sql
security definer
set search_path = public
as $$
    select jsonb_build_object(
        'id', request.id, 'request_number', request.request_number,
        'status', request.status, 'reason', request.reason,
        'borrow_date', request.borrow_date, 'due_date', request.due_date,
        'return_date', request.return_date, 'return_location', request.return_location,
        'admin_remarks', request.admin_remarks, 'approved_at', request.approved_at,
        'rejected_at', request.rejected_at, 'cancelled_at', request.cancelled_at,
        'borrowed_at', request.borrowed_at, 'returned_at', request.returned_at,
        'items', coalesce((
            select jsonb_agg(jsonb_build_object(
                'id', item.id, 'inventory_id', item.inventory_id,
                'item_code', inventory.item_code, 'name', inventory.name,
                'brand', inventory.brand, 'unit', inventory.unit,
                'quantity', item.quantity, 'condition_borrowed', item.condition_borrowed,
                'condition_returned', item.condition_returned, 'is_returned', item.is_returned,
                'returned_at', item.returned_at, 'damage_notes', item.damage_notes,
                'return_evidence', (
                    select jsonb_build_object(
                        'file_name', evidence.file_name,
                        'uploaded_at', evidence.uploaded_at
                    )
                    from public.borrow_return_evidences as evidence
                    where evidence.id = item.return_evidence_id
                )
            ) order by item.id)
            from public.borrow_items as item
            left join public.inventories as inventory on inventory.id = item.inventory_id
            where item.borrow_request_id = request.id
        ), '[]'::jsonb)
    ) from public.borrow_requests as request
    where request.id = p_request_id and request.deleted_at is null;
$$;

create or replace function public.mobile_list_borrow_requests()
returns jsonb
language plpgsql
security definer
set search_path = public
as $$
declare
    v_employee_id bigint;
    v_requests jsonb;
begin
    v_employee_id := public.mobile_assert_access();
    select coalesce(jsonb_agg(public.mobile_request_payload(request.id)
                              order by request.created_at desc), '[]'::jsonb)
    into v_requests
    from public.borrow_requests as request
    where request.employee_id = v_employee_id and request.deleted_at is null;
    return v_requests;
end;
$$;

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

create or replace function public.mobile_create_borrow_request(
    p_borrow_date date, p_due_date date, p_reason text, p_items jsonb
)
returns jsonb
language plpgsql
security definer
set search_path = public
as $$
declare
    v_employee_id bigint;
    v_request_id bigint;
    v_item record;
    v_inventory public.inventories%rowtype;
    v_requested_count integer;
    v_distinct_count integer;
begin
    v_employee_id := public.mobile_assert_access();
    if p_borrow_date is null or p_borrow_date < current_date then
        raise exception 'Borrow date must be today or later.' using errcode = '22023';
    end if;
    if p_due_date is null or p_due_date <= p_borrow_date then
        raise exception 'Due date must be after the borrow date.' using errcode = '22023';
    end if;
    if p_reason is null or length(trim(p_reason)) = 0 or length(p_reason) > 2000 then
        raise exception 'A reason of up to 2000 characters is required.' using errcode = '22023';
    end if;
    if p_items is null or jsonb_typeof(p_items) <> 'array' or jsonb_array_length(p_items) = 0 then
        raise exception 'At least one inventory item is required.' using errcode = '22023';
    end if;

    select count(*), count(distinct item.inventory_id)
    into v_requested_count, v_distinct_count
    from jsonb_to_recordset(p_items) as item(inventory_id bigint, quantity integer);
    if exists (
        select 1 from jsonb_to_recordset(p_items) as item(inventory_id bigint, quantity integer)
        where item.inventory_id is null or item.quantity is null or item.quantity < 1
    ) then
        raise exception 'Each item needs a valid inventory id and positive quantity.' using errcode = '22023';
    end if;
    if v_requested_count <> v_distinct_count then
        raise exception 'Each inventory item can only be requested once.' using errcode = '22023';
    end if;

    perform inventory.id
    from public.inventories as inventory
    join jsonb_to_recordset(p_items) as requested(inventory_id bigint, quantity integer)
      on requested.inventory_id = inventory.id
    order by inventory.id
    for update of inventory;

    for v_item in
        select * from jsonb_to_recordset(p_items) as item(inventory_id bigint, quantity integer)
        order by inventory_id
    loop
        select * into v_inventory from public.inventories where id = v_item.inventory_id;
        if not found or v_inventory.deleted_at is not null or not v_inventory.is_active
           or v_inventory.status <> 'available' then
            raise exception 'One of the selected items is no longer available.' using errcode = 'P0001';
        end if;
        if v_inventory.quantity < v_item.quantity then
            raise exception 'Insufficient stock for %.', v_inventory.name using errcode = 'P0001';
        end if;
    end loop;

    insert into public.borrow_requests (
        request_number, employee_id, status, reason, borrow_date, due_date, created_at, updated_at
    ) values (
        'BR-' || upper(replace(gen_random_uuid()::text, '-', '')),
        v_employee_id, 'pending', trim(p_reason), p_borrow_date, p_due_date, now(), now()
    ) returning id into v_request_id;

    for v_item in
        select * from jsonb_to_recordset(p_items) as item(inventory_id bigint, quantity integer)
        order by inventory_id
    loop
        insert into public.borrow_items (
            borrow_request_id, inventory_id, quantity, reserved_quantity,
            condition_borrowed, created_at, updated_at
        ) values (
            v_request_id, v_item.inventory_id, v_item.quantity, v_item.quantity,
            'good', now(), now()
        );
        update public.inventories set quantity = quantity - v_item.quantity, updated_at = now()
        where id = v_item.inventory_id;
    end loop;

    return public.mobile_request_payload(v_request_id);
end;
$$;

create or replace function public.mobile_cancel_borrow_request(p_request_id bigint)
returns jsonb
language plpgsql
security definer
set search_path = public
as $$
declare
    v_employee_id bigint;
    v_request public.borrow_requests%rowtype;
    v_reserved record;
begin
    v_employee_id := public.mobile_assert_access();
    select * into v_request from public.borrow_requests
    where id = p_request_id and employee_id = v_employee_id and deleted_at is null for update;
    if not found then
        raise exception 'Borrow request not found.' using errcode = 'P0002';
    end if;
    if v_request.status <> 'pending' then
        raise exception 'Only pending requests can be cancelled.' using errcode = 'P0001';
    end if;

    for v_reserved in
        select item.id, item.inventory_id, item.reserved_quantity
        from public.borrow_items as item
        where item.borrow_request_id = p_request_id and item.reserved_quantity > 0
        for update
    loop
        update public.inventories set quantity = quantity + v_reserved.reserved_quantity, updated_at = now()
        where id = v_reserved.inventory_id;
        update public.borrow_items set reserved_quantity = 0, updated_at = now()
        where id = v_reserved.id;
    end loop;

    update public.borrow_requests set status = 'cancelled', cancelled_at = now(), updated_at = now()
    where id = p_request_id;
    return public.mobile_request_payload(p_request_id);
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
        if not v_borrow_item.is_returned then
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

    update public.borrow_requests set return_location = trim(p_return_location), updated_at = now()
    where id = p_request_id;
    if not exists (
        select 1 from public.borrow_items where borrow_request_id = p_request_id and is_returned = false
    ) then
        update public.borrow_requests
        set status = 'returned', return_date = current_date, returned_at = now(), updated_at = now()
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

create or replace function public.mobile_update_employee_name(p_name text)
returns jsonb
language plpgsql
security definer
set search_path = public
as $$
declare
    v_employee_id bigint;
begin
    v_employee_id := public.mobile_assert_access();
    if p_name is null or length(trim(p_name)) = 0 or length(p_name) > 255 then
        raise exception 'Enter a valid name.' using errcode = '22023';
    end if;
    update public.users set name = trim(p_name), updated_at = now() where id = v_employee_id;
    update auth.users
    set raw_user_meta_data = coalesce(raw_user_meta_data, '{}'::jsonb)
            || jsonb_build_object('name', trim(p_name)),
        updated_at = now()
    where id = auth.uid();
    return public.mobile_employee_profile();
end;
$$;

revoke all on public.users, public.inventories, public.borrow_requests, public.borrow_items,
    public.roles, public.model_has_roles from anon, authenticated;

alter table public.users enable row level security;
alter table public.inventories enable row level security;
alter table public.borrow_requests enable row level security;
alter table public.borrow_items enable row level security;
alter table public.roles enable row level security;
alter table public.model_has_roles enable row level security;

revoke execute on function public.link_mobile_employee() from public, anon, authenticated;
revoke execute on function public.sync_employee_password_to_auth() from public, anon, authenticated;
revoke execute on function public.mobile_employee_id() from public, anon, authenticated;
revoke execute on function public.mobile_assert_access() from public, anon, authenticated;
revoke execute on function public.mobile_request_payload(bigint) from public, anon, authenticated;

revoke execute on function public.mobile_employee_profile() from public, anon;
revoke execute on function public.mobile_inventory_lookup(text) from public, anon;
revoke execute on function public.mobile_inventory_search(text, integer) from public, anon;
revoke execute on function public.mobile_list_borrow_requests() from public, anon;
revoke execute on function public.mobile_dashboard() from public, anon;
revoke execute on function public.mobile_create_borrow_request(date, date, text, jsonb) from public, anon;
revoke execute on function public.mobile_cancel_borrow_request(bigint) from public, anon;
revoke execute on function public.mobile_returnable_items() from public, anon;
revoke execute on function public.mobile_return_borrow_items(bigint, text, text, jsonb) from public, anon;
revoke execute on function public.mobile_update_employee_name(text) from public, anon;

grant execute on function public.mobile_employee_profile() to authenticated;
grant execute on function public.mobile_inventory_lookup(text) to authenticated;
grant execute on function public.mobile_inventory_search(text, integer) to authenticated;
grant execute on function public.mobile_list_borrow_requests() to authenticated;
grant execute on function public.mobile_dashboard() to authenticated;
grant execute on function public.mobile_create_borrow_request(date, date, text, jsonb) to authenticated;
grant execute on function public.mobile_cancel_borrow_request(bigint) to authenticated;
grant execute on function public.mobile_returnable_items() to authenticated;
grant execute on function public.mobile_return_borrow_items(bigint, text, text, jsonb) to authenticated;
grant execute on function public.mobile_update_employee_name(text) to authenticated;