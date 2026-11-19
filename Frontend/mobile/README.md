# RGV Employee App

Flutter client for the RGV Multi-Tech Services employee workflows: sign in, view
the dashboard, browse and scan inventory, request equipment, and return it.

The app talks **only** to the team's Laravel REST API. There is no Supabase
dependency, no database driver, and no service key in the client.

## Requirements

- Flutter 3.24+ (Dart 3.12+)
- A running Laravel backend (see the repository `README.md`)

## Configuration

Debug builds discover the Laravel API address automatically from the current
local network. The phone and computer must share a Wi-Fi/LAN that permits
device-to-device traffic. On Windows, run
`scripts/allow-mobile-api-local-subnet.ps1` once from the repository root in an
Administrator PowerShell session. The firewall rules use `LocalSubnet`, not a
fixed Wi-Fi address.

```bash
# Android emulator (10.0.2.2 is the host machine)
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000

# Physical device on the same Wi-Fi/LAN (automatic discovery)
flutter run

# Release build (HTTPS is required)
flutter build apk --dart-define=API_BASE_URL=https://your-domain.example
```

Android emulators can use `http://10.0.2.2:8000` as a build-time override.
Release builds require a stable HTTPS URL in `API_BASE_URL`; local discovery is
for development on the same network as Laravel.

The native app sends login and data requests directly to Laravel at
`/api/v1/mobile/*`. The `Frontend/app` Vite server on port 5174 serves the
browser SPA; it does not serve or start the Flutter app and is not needed for
native login. Keep the Laravel API from `composer run dev` running on port 8000,
then run `flutter run` from `Frontend/mobile` or install a newly built APK. The
discovery responder starts with the Laravel dev command, so changing Wi-Fi
does not require changing the APK's URL.

## Account provisioning

An administrator creates the employee in the staff portal at
`/admin/users/create` and assigns the `employee` role. That employee then signs
in here with the same email and password. Laravel is the single source of
identity - no external auth provider is involved.

## How it talks to the backend

All requests go through `lib/core/network/mobile_api_client.dart`:

| App action | Endpoint |
|---|---|
| Sign in | `POST /api/v1/mobile/auth/login` |
| Verify MFA | `POST /api/v1/mobile/auth/mfa/verify` |
| Sign out | `POST /api/v1/mobile/auth/logout` |
| Profile | `GET /api/v1/mobile/me`, `PATCH .../profile` |
| Change password | `PUT .../password` |
| Dashboard | `GET .../dashboard`, `POST .../dashboard/recent-requests/{id}/dismiss` |
| Inventory | `GET .../inventory/search`, `GET .../inventory/lookup` |
| Borrowing | `GET/POST .../borrow-requests`, `POST .../{id}/cancel` |
| Returns | `GET .../borrow-requests/returnable`, `POST .../{id}/return` |

The bearer token is stored with `shared_preferences`. A `401` clears it and
returns the user to the sign-in screen.

## Live updates

Laravel has no websocket transport, so the borrow history and return screens
refresh on a 20-second poll (plus pull-to-refresh) instead of a realtime
subscription.

## MFA

The app **verifies** MFA codes. Enrolment happens in the staff portal
(Profile -> manage MFA) because that is where Laravel generates the TOTP secret.

## Development

```bash
flutter pub get
flutter analyze
flutter test
dart format --set-exit-if-changed lib test
```

## Building a release APK

```bash
flutter build apk --release --dart-define=API_BASE_URL=https://your-domain.example
```

The artifact is written to `build/app/outputs/flutter-apk/app-release.apk`.
