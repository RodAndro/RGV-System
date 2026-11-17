# RGV Employee App

The Flutter app connects directly to the same Supabase project as Laravel. It
does not call Laravel or the standalone `mobile-service` at runtime. Supabase
Auth handles employee sign-in; restricted PostgreSQL RPCs handle employee
profiles, inventory, borrow requests, and returns.

## Supabase setup

1. Configure Laravel's `SUPABASE_URL`, `SUPABASE_PUBLISHABLE_KEY`, and
   server-only `SUPABASE_SECRET_KEY`. Older projects can use the legacy
   `SUPABASE_SERVICE_ROLE_KEY` JWT instead. Never put either server key in
   Flutter or source control.
2. Run the Laravel migrations against the shared Supabase database.
3. Apply [`database/supabase/mobile_backend.sql`](../database/supabase/mobile_backend.sql)
   as the Supabase database owner. It links Auth identities to active employee
   rows and installs the RPCs and grants used by this app. The script asks
   Supabase to reload its API schema so the new RPCs are available to Flutter.
4. Create or update employee accounts through Laravel's admin UI. When linking
   an existing employee who has no Supabase Auth identity, set a password in
   the admin edit form so Laravel can create and link the Auth account. Existing
   matching Auth users are linked by email.

## Build and run

The Flutter client receives only the public Supabase URL and publishable key at
build time:

```powershell
flutter pub get
flutter run `
  --dart-define=SUPABASE_URL=https://fywtxrptaqcmkzsgfehp.supabase.co `
  --dart-define=SUPABASE_ANON_KEY=YOUR_SUPABASE_PUBLISHABLE_KEY
```

Build a debug APK with the same defines:

```powershell
flutter build apk --debug `
  --dart-define=SUPABASE_URL=https://fywtxrptaqcmkzsgfehp.supabase.co `
  --dart-define=SUPABASE_ANON_KEY=YOUR_SUPABASE_PUBLISHABLE_KEY
```

Laravel may be shut down while the native app is in use. Both applications
read and write the shared Supabase database, so changes become visible to the
other application through that database. Supabase must remain reachable for
authentication and server-side transactions.

## App behavior

- Supabase Auth sessions and MFA are managed by `supabase_flutter`.
- The app checks employee status and role through restricted database RPCs.
- Dashboard, inventory, borrowing, cancellation, returns, profile updates,
  and password changes always read from or write to Supabase directly.
- The app does not cache business data or queue transactions. If Supabase is
  unreachable, the operation fails visibly and is not reported as saved.
- Laravel and Flutter read and write the same Supabase database, so Laravel
  sees completed mobile transactions when its pages or reports refresh.
- The client contains no PostgreSQL connection string or privileged Supabase
  key.

## Verify

```powershell
flutter analyze
flutter test
flutter build apk --debug `
  --dart-define=SUPABASE_URL=https://fywtxrptaqcmkzsgfehp.supabase.co `
  --dart-define=SUPABASE_ANON_KEY=YOUR_SUPABASE_PUBLISHABLE_KEY
```
