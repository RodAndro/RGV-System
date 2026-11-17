-- Provision once as the PostgreSQL owner with psql variables MOBILE_DB_NAME and
-- MOBILE_DB_PASSWORD. This role is for the server only.
-- Policies grant this server principal row visibility only; table grants remain scoped.
\prompt -s MOBILE_DB_PASSWORD 'Generate a password for rgv_mobile_api: '
BEGIN;
CREATE ROLE rgv_mobile_api LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT NOBYPASSRLS PASSWORD :'MOBILE_DB_PASSWORD';
GRANT CONNECT ON DATABASE :"MOBILE_DB_NAME" TO rgv_mobile_api;
GRANT USAGE ON SCHEMA public TO rgv_mobile_api;

GRANT SELECT (id,name,email,password,phone,avatar_path,is_active,mfa_enabled,mfa_type,mfa_secret,mfa_recovery_codes)
    ON public.users TO rgv_mobile_api;
GRANT UPDATE (name,password,mfa_recovery_codes,last_login_at,updated_at) ON public.users TO rgv_mobile_api;
GRANT SELECT (id,name,guard_name) ON public.roles TO rgv_mobile_api;
GRANT SELECT (role_id,model_type,model_id) ON public.model_has_roles TO rgv_mobile_api;
GRANT SELECT (id,item_code,name,brand,description,category_id,quantity,unit,status,condition,is_active,deleted_at,qr_token,location,low_stock_threshold)
    ON public.inventories TO rgv_mobile_api;
GRANT SELECT (id,name) ON public.inventory_categories TO rgv_mobile_api;
GRANT UPDATE (quantity,updated_at) ON public.inventories TO rgv_mobile_api;
GRANT SELECT ON public.borrow_requests,public.borrow_items TO rgv_mobile_api;
GRANT INSERT (request_number,employee_id,status,reason,borrow_date,due_date,created_at,updated_at) ON public.borrow_requests TO rgv_mobile_api;
GRANT UPDATE (status,cancelled_at,return_location,return_date,returned_at,updated_at) ON public.borrow_requests TO rgv_mobile_api;
GRANT INSERT (borrow_request_id,inventory_id,quantity,reserved_quantity,condition_borrowed,created_at,updated_at) ON public.borrow_items TO rgv_mobile_api;
GRANT UPDATE (reserved_quantity,condition_returned,damage_notes,is_returned,returned_at,updated_at) ON public.borrow_items TO rgv_mobile_api;
GRANT SELECT,DELETE ON public.personal_access_tokens TO rgv_mobile_api;
GRANT INSERT (tokenable_type,tokenable_id,name,token,abilities,expires_at,created_at,updated_at) ON public.personal_access_tokens TO rgv_mobile_api;
GRANT UPDATE (last_used_at,updated_at) ON public.personal_access_tokens TO rgv_mobile_api;
GRANT SELECT ON public.mobile_return_idempotency TO rgv_mobile_api;
GRANT INSERT ON public.mobile_return_idempotency TO rgv_mobile_api;
GRANT SELECT,INSERT,UPDATE ON public.mobile_borrow_idempotency TO rgv_mobile_api;
GRANT SELECT (user_id,type,in_app_enabled,email_enabled) ON public.notification_preferences TO rgv_mobile_api;
GRANT INSERT ON public.notifications,public.activity_log TO rgv_mobile_api;
GRANT SELECT (id,user_id,logged_out_at,logged_in_at),INSERT (user_id,ip_address,user_agent,logged_in_at,created_at,updated_at),UPDATE (logged_out_at,updated_at)
    ON public.login_history TO rgv_mobile_api;
GRANT INSERT ON public.audit_logs TO rgv_mobile_api;
GRANT SELECT (id,checksum) ON public.audit_logs TO rgv_mobile_api;
GRANT SELECT,INSERT,UPDATE,DELETE ON public.mobile_login_attempts TO rgv_mobile_api;
GRANT SELECT,INSERT,UPDATE,DELETE ON public.mobile_api_rate_limits TO rgv_mobile_api;

DO $$
DECLARE tbl text; seq text;
BEGIN
  FOREACH tbl IN ARRAY ARRAY['personal_access_tokens','notifications','activity_log','audit_logs','login_history','borrow_requests','borrow_items'] LOOP
    seq := pg_get_serial_sequence(format('public.%I', tbl), 'id');
    IF seq IS NOT NULL THEN EXECUTE format('GRANT USAGE,SELECT ON SEQUENCE %s TO rgv_mobile_api', seq); END IF;
  END LOOP;
END $$;

-- The mobile SQL backend, if previously installed, enables RLS on these tables.
-- Restrict policy applicability to this database role; do not grant these policies to client roles.
DO $$
DECLARE tbl text;
BEGIN
  FOREACH tbl IN ARRAY ARRAY['users','roles','model_has_roles','inventories','inventory_categories','borrow_requests','borrow_items','mobile_return_idempotency','mobile_borrow_idempotency'] LOOP
    IF EXISTS (SELECT 1 FROM pg_class c JOIN pg_namespace n ON n.oid=c.relnamespace WHERE n.nspname='public' AND c.relname=tbl AND c.relrowsecurity) THEN
      EXECUTE format('DROP POLICY IF EXISTS rgv_mobile_service_access ON public.%I', tbl);
      EXECUTE format('CREATE POLICY rgv_mobile_service_access ON public.%I TO rgv_mobile_api USING (true) WITH CHECK (true)', tbl);
    END IF;
  END LOOP;
END $$;

COMMIT;
