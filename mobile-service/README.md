# Standalone Flutter API

This service serves the Flutter Android client at `/api/v1/mobile`. It is a separate FastAPI process and container; it imports no Laravel code and calls no Laravel route. Laravel may be stopped while this service and PostgreSQL remain available. The Flutter app still uses HTTPS REST and never receives database credentials.

## Shared data and security

- It reads and updates the existing `users`, Spatie `roles` / `model_has_roles`, inventory, borrow, notification, and audit tables.
- Login verifies the Laravel bcrypt hash in place. A successful login does not copy or rewrite the password. A password change updates the shared `users.password` using a PHP-compatible bcrypt hash.
- MFA reads the existing `mfa_enabled`, `mfa_secret`, and `mfa_recovery_codes` fields. TOTP uses the same RFC 6238 settings as Laravel. Recovery codes are consumed once in the shared row; Laravel's mobile MFA route was updated to consume the same code.
- The service writes opaque bearer tokens to `personal_access_tokens` using Sanctum's SHA-256 token format and abilities. Pending MFA tokens only have `mfa-verify`; employee tokens have `employee`; each expires after 30 days.
- Borrow stock reservation, cancellation, and return updates use PostgreSQL transactions and row locks. Borrow creation requires an idempotency key and persists the confirmed response in `mobile_borrow_idempotency`; repeated cancellations are safe. Return retries use the existing `mobile_return_idempotency` table. Request ownership and active employee status are checked on every authenticated request.
- Mobile mutations write both Laravel `audit_logs` checksums and Spatie `activity_log` rows. `AUDIT_HMAC_KEY` must be the exact server-side Laravel `APP_KEY` value so both writers extend the same checksum chain. This is a shared secret configuration, not a runtime call to Laravel.
- New-request and low-stock in-app alerts are written to `notifications` and respect in-app preferences. Return-complete in-app and email alerts respect employee preferences. Optional SMTP settings send request-created, request-cancelled, and completed-return email.

The service's database role is separate from Laravel's role, is `NOSUPERUSER`, `NOCREATEDB`, `NOCREATEROLE`, `NOINHERIT`, and `NOBYPASSRLS`, and receives grants only on the tables/columns used here. Flutter receives no database secret. The service database principal can read password and MFA verifier columns because verification happens server-side; it cannot change roles or account active status.

## One-time additive database setup

Back up the database using the normal deployment procedure first. The SQL creates one small login-throttle table, a dedicated server-only role, scoped grants, and RLS policies for that role where RLS is enabled. It does not change existing rows or drop application objects. Do not apply `database/supabase/mobile_backend.sql`; that file is legacy.

1. Apply the additive table migration as the database owner:

   ```bash
   psql "$DATABASE_URL" -v ON_ERROR_STOP=1 -f mobile-service/sql/001_mobile_service.sql
   ```

2. Provision the service principal and table grants once. The script prompts without echoing for a generated database password. Set the database name (`postgres` for the current Supabase setup) in the psql variable:

   ```bash
   psql "$DATABASE_URL" -v ON_ERROR_STOP=1 -v MOBILE_DB_NAME=postgres -f mobile-service/sql/002_mobile_service_grants.template.sql
   ```

   Store that password only in the mobile service secret manager/environment. If the role already exists, do not rerun the role-creation script; rotate it with `ALTER ROLE rgv_mobile_api PASSWORD ...` in a secure owner session and recheck grants. Review and adapt the database name and Supabase RLS configuration before applying.

The current table grants assume the repository migrations are already applied, including `last_login_at`, the `qr_token`, reservation fields, return location, `activity_log`, `audit_logs`, `mobile_return_idempotency`, and `mobile_borrow_idempotency`. Apply pending additive Laravel migrations before provisioning the service. `001_mobile_service.sql` also creates the service-owned idempotency table if it is absent. No `migrate:fresh`, seed, drop, truncate, or data reset is needed.

## Configure and run independently

Create `mobile-service/.env` from `.env.example`. Set the dedicated DB host/user/password, TLS (`DB_SSLMODE=verify-full` and the provider CA when needed), and `AUDIT_HMAC_KEY` to the exact existing Laravel `APP_KEY` string from the secret manager. When using the Supabase Session pooler, use the exact pooler username (the database role plus `.<project-ref>`). Do not copy database credentials into Flutter or commit either secret.

With Docker Compose on the deployment host:

```bash
docker compose -f mobile-service/compose.yaml up -d --build
```

The service binds to loopback port 8081 by default. Route the mobile API hostname over HTTPS through the deployment reverse proxy to this private listener. Apply the same IP/user rate limiting at the proxy if it is exposed directly; keep port 8081 private. For local development, install the pinned requirements and run:

```powershell
cd mobile-service
Copy-Item .env.example .env
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
uvicorn app.main:app --host 0.0.0.0 --port 8081
```

For Android emulator local testing, the Flutter base URL is `http://10.0.2.2:8081/api/v1/mobile`. Release builds require HTTPS and should use the API hostname routed to this service.

For a physical phone on the same network, `.\run-local.ps1` loads `mobile-service\.env`, prints the reachable LAN URLs, and starts the service on `0.0.0.0:8081`. Allow inbound TCP 8081 through the computer's firewall and enter the printed `http://<LAN-IP>:8081/api/v1/mobile` address in the app's **Server** setting on the sign-in screen.

### Required running services

For mobile workflows, keep the independent `mobile-api` service, the shared PostgreSQL service, and the HTTPS reverse proxy reachable. Laravel, Vite, Ollama, queues, and Laravel web workers are not required for Flutter login, MFA, dashboard, inventory, borrowing, returns, profile, or password changes. The Laravel web application continues to use the same shared tables when it is running.

## Tests

Install test dependencies with `pip install -r requirements-dev.txt`, then run `pytest -q`. Unit tests cover bcrypt compatibility, TOTP, and token generation. PostgreSQL integration tests use `TEST_DATABASE_URL`, create a random schema, and refuse any database name that does not end in `_test`. They exercise shared account login/authorization, MFA challenge restrictions, stock reservation, return, and idempotency without calling Laravel. No production database URL should be used for integration tests.

The database-backed integration suite must be run before production deployment against a disposable PostgreSQL database created from the current Laravel migrations. If `TEST_DATABASE_URL` is unset, those tests are reported as skipped; unit tests alone do not certify database workflow behavior.
