# RGV Employee (Flutter / Android)

Android mobile app for RGV Multi-Tech Services employees. Talks to the Laravel
API under `/api/v1/mobile` — it never connects to the database directly.

## Run

```bash
# Local dev (Android emulator reaches the host via 10.0.2.2):
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1/mobile

# Staging / production (HTTPS required):
flutter run --dart-define=API_BASE_URL=https://api.example.com/api/v1/mobile
```

Release builds fail fast if `API_BASE_URL` is not HTTPS (see
`lib/core/config/app_config.dart`). Plain-HTTP is only allowed in debug builds
via the debug-only Android manifest overlay.

## Structure

Feature-oriented layout under `lib/`:

- `core/` — config, theme, HTTP client, error handling, secure token storage
- `features/auth/` — login, MFA, logout, restored sessions
- `features/dashboard/` — employee summary + action tiles
- `features/inventory/` — QR scan + manual item lookup
- `features/borrow/` — borrow form (dates, reason, review) + history
- `features/return/` — select items, capture photo, submit return
- `features/account/` — name/password settings
- `shared/widgets/` — reusable status chips and async states

## Key dependencies

`http`, `flutter_secure_storage` (token at rest), `mobile_scanner` (QR),
`image_picker` (return proof), `provider` (state), `intl` (dates), `uuid`
(return idempotency keys).

## Verify

```bash
flutter analyze
flutter test
flutter build apk --debug
```

## Android permissions

- `INTERNET` — required in all builds (added to the main manifest).
- `CAMERA` — QR scanning and return-proof photos; declared optional via
  `<uses-feature ... required="false">` so devices without a camera can still
  use manual code entry.
