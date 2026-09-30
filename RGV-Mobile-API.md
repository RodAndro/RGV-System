# RGV Mobile API

> Employee mobile API for the Flutter Android app. Laravel remains the trusted
> API and business-rules layer; the app never connects to the database directly.
>
> Base path: `/api/v1/mobile` · Auth: Laravel Sanctum bearer tokens.

## Authentication

All routes except login use `Authorization: Bearer <token>`. The token is
issued by Sanctum. Responses use `snake_case` keys (these routes are not under
the `camel.json` middleware). Errors follow Laravel's shape:

```json
{ "message": "…", "errors": { "field": ["…"] } }
```

`401` = unauthenticated, `403` = wrong role/inactive, `409` = business-rule
conflict (e.g. insufficient stock), `422` = validation, `429` = throttled,
`502` = upload failure.

## Endpoints

| Method | URI | Auth | Purpose |
|--------|-----|------|---------|
| POST | `/auth/login` | — | Login; returns token or MFA challenge |
| POST | `/auth/mfa/verify` | mfa-token | Verify 6-digit code, issue token |
| POST | `/auth/logout` | bearer | Revoke current token |
| GET | `/me` | bearer | Current employee |
| GET | `/dashboard` | bearer | Summary + recent requests |
| GET | `/inventory/lookup?code=` | bearer | Resolve QR/item code to live item |
| GET | `/inventory/search?q=` | bearer | Search active inventory |
| GET | `/borrow-requests` | bearer | Employee's requests |
| POST | `/borrow-requests` | bearer | Create request (reserves stock) |
| GET | `/borrow-requests/{id}` | bearer | Single request |
| POST | `/borrow-requests/{id}/cancel` | bearer | Cancel pending (releases stock) |
| GET | `/borrow-requests/returnable` | bearer | Borrowed items eligible for return |
| POST | `/borrow-requests/{id}/return` | bearer | Return items with proof photo |
| PATCH | `/profile` | bearer | Update name (email is read-only) |
| PUT | `/password` | bearer | Change password |

### `POST /auth/login`

Request: `{ "email", "password", "device_name" }`.

- No MFA → `{ "token", "user" }`
- MFA enabled → `{ "mfa_required": true, "mfa_type", "mfa_token" }`

Rejects inactive accounts and non-employee roles with `422`.

### `POST /borrow-requests`

Request: `{ "borrow_date", "due_date", "reason", "items": [{ "inventory_id", "quantity" }] }`.

A pending request **reserves** its stock (decrements `quantity`, records
`reserved_quantity`). Approval does not decrement again; rejection/cancellation
releases the reservation. Concurrent over-reservation returns `409`.

### `POST /borrow-requests/{id}/return`

Multipart: `idempotency_key`, `photo` (image), and
`items[i][borrow_item_id|condition_returned|damage_notes]`.

The photo is uploaded to Google Drive server-side. Stock is restored only after
the upload succeeds, and retries are idempotent (stock is never restored twice).

## Environment variables (placeholders only — never commit real values)

```dotenv
# Supabase PostgreSQL (session pooler shown; use the direct host if reachable)
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.<your-project-ref>
DB_PASSWORD=<supplied-by-deployment-only>
DB_SSLMODE=require

# Sanctum
SANCTUM_STATEFUL_DOMAINS=

# Google Drive return-proof uploads (service account)
GOOGLE_DRIVE_CREDENTIALS_PATH=<absolute-path-to-service-account-json>
GOOGLE_DRIVE_CREDENTIALS_JSON=
GOOGLE_DRIVE_FOLDER_ID=1_JL1cnADajKzMcx_HAKlEbiLdbNjyiq4
GOOGLE_DRIVE_SUBJECT=
```
