# RGV Employee (Flutter / Android)

Android mobile app for RGV Multi-Tech Services employees. Supabase Auth handles
login and restricted PostgreSQL RPCs handle employee workflows. The Laravel
website and app share the same Supabase database.

## Run

```bash
flutter pub get
flutter run --dart-define=SUPABASE_URL=https://YOUR_PROJECT_REF.supabase.co --dart-define=SUPABASE_ANON_KEY=YOUR_PUBLISHABLE_KEY
```

The URL and publishable key are in Supabase project settings. Never put a
service-role key in the app. Release builds require HTTPS.

The Laravel server provisions native employee logins through Supabase Auth when
an admin creates or updates an employee. Configure `SUPABASE_URL` and the
server-only `SUPABASE_SECRET_KEY` in the Laravel environment; never use that
secret key in a Flutter build.

## Supabase setup

1. Point Laravel's PostgreSQL connection at this same Supabase project and run
  `php artisan migrate --force`.
2. Run [`../database/supabase/mobile_backend.sql`](../database/supabase/mobile_backend.sql)
  in Supabase SQL Editor after Laravel migrations complete.
3. Invite each existing active employee in Supabase Auth using the same email
  as the Laravel employee record, and require email confirmation. Laravel
  password hashes are not imported; employees set a new password from the
  invitation. The SQL trigger then mirrors the Supabase bcrypt hash to Laravel.
4. Employees whose Laravel record requires MFA are prompted to enroll a TOTP
  factor in the app. Mobile data RPCs require AAL2 for those accounts.

The SQL setup links confirmed identities to active employees and grants the
mobile role access only through checked RPCs. Mobile actions continue while
the website or Laravel process is unavailable if Supabase remains reachable.
The website reads the same committed rows when its pages load, so no data-copy
or sync worker is required. Device-offline submission is not queued.

## File storage

The native app reads structured rows through RPCs and does not upload or
display files. Booking attachments and inventory image uploads are not
supported by the current application. Their nullable database path columns or
files from older deployments may still exist, but current screens and RPCs do
not use them. Employee avatar paths are separate Laravel-managed data and are
not shared with the native app.
- **Generated exports, import error reports, and backups** are
  per-instance artifacts on the local disk; their metadata rows
  (`export_logs`, `import_logs`, `backup_monitoring`) are shared
  through the database. Backups are disaster-recovery copies and
  belong outside the request path; they are not migrated blindly.
- **Temporary processing files** (queued job payloads, scratch
  imports) remain local by design.

## Structure

Feature-oriented layout under `lib/`:

- `core/` — Supabase config, theme, and error handling
- `features/auth/` — login, MFA, logout, sessions restored across app restarts
- `features/dashboard/` — employee summary + action tiles
- `features/inventory/` — QR scan, manual lookup, and searchable catalog
- `features/borrow/` — multi-item request form (dates, reason, review) + history
- `features/return/` — select items, record condition/location, submit return
- `features/account/` — name/password settings
- `shared/widgets/` — reusable status chips and async states

## Key dependencies

`supabase_flutter`, `mobile_scanner` (QR), `provider` (state), `intl` (dates),
`uuid` (return idempotency keys).

Borrow history refreshes on entry, pull-to-refresh, and every 30 seconds while
open. Native RPC writes share the Laravel tables but do not execute Eloquent
observers, queued email, or Laravel activity logging. Inventory images and
return photo uploads are not supported by the native app; return payloads
include the evidence file name and upload time for returns recorded through
the website.

## Verify

```bash
flutter analyze
flutter test
flutter build apk --debug
```

## Android permissions

- `INTERNET` — required in all builds (added to the main manifest).
- `CAMERA` — QR scanning; declared optional via
  `<uses-feature ... required="false">` so devices without a camera can still
  use manual code entry.
