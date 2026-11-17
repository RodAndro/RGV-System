-- Apply once to the existing PostgreSQL database as its database owner.
-- This is additive. It does not modify or delete application records.
BEGIN;

CREATE TABLE IF NOT EXISTS public.mobile_login_attempts (
    key char(64) PRIMARY KEY,
    attempts smallint NOT NULL DEFAULT 0 CHECK (attempts >= 0),
    window_started_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS mobile_login_attempts_window_started_at_index
    ON public.mobile_login_attempts(window_started_at);

CREATE TABLE IF NOT EXISTS public.mobile_api_rate_limits (
    key char(64) PRIMARY KEY,
    hits integer NOT NULL DEFAULT 0 CHECK (hits >= 0),
    window_started_at timestamptz NOT NULL,
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS public.mobile_borrow_idempotency (
    employee_id bigint NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    idempotency_key varchar(64) NOT NULL,
    request_hash char(64) NOT NULL,
    response_payload jsonb,
    created_at timestamptz NOT NULL DEFAULT now(),
    PRIMARY KEY (employee_id, idempotency_key)
);
ALTER TABLE public.mobile_borrow_idempotency ENABLE ROW LEVEL SECURITY;
REVOKE ALL ON public.mobile_borrow_idempotency FROM anon, authenticated;

-- Create the service principal out-of-band with a randomly generated password.
-- Example (run separately in psql, replacing the value from a secret manager):
-- CREATE ROLE rgv_mobile_api LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOINHERIT NOBYPASSRLS PASSWORD '...';
-- The API only receives this principal's credential, never the database owner credential.

COMMIT;
