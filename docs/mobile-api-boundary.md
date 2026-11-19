# Mobile app and API boundary

The Flutter employee app talks **only** to the team's Laravel REST API. It
holds no database credentials, no service keys and no third-party auth SDK.

## Request path

```text
Flutter app --HTTPS + Sanctum bearer token--> Laravel /api/v1/mobile/* --> MySQL
```

## Rules

- The app never connects to MySQL directly.
- The app never holds a database connection string or a privileged key.
- All authorisation is enforced server-side; the app only decides which
  controls to show.
- The bearer token is stored on the device with `shared_preferences`. A `401`
  clears it and returns the user to the sign-in screen.
- Logout, password change and account deactivation revoke the token server-side.

## Account provisioning

An administrator creates the employee at `/admin/users/create`. Laravel hashes
the password and assigns the `employee` role; that account can immediately
sign into the app with the same email and password. Laravel is the single
source of identity.

## Endpoints used by the app

| Action | Endpoint |
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

## Live updates

Laravel has no websocket transport, so borrow history and return screens poll
every 20 seconds (plus pull-to-refresh) instead of subscribing to row changes.

## MFA

The app verifies MFA codes. Enrolment happens in the staff portal (Profile ->
manage MFA), because that is where Laravel generates the TOTP secret.

## Configuration

For a physical phone, `composer run dev` starts Laravel and a UDP discovery
responder. The app finds the current API address when the phone and computer
share a Wi-Fi/LAN that permits device-to-device traffic. On Windows, run
`scripts/allow-mobile-api-local-subnet.ps1` once as Administrator; its rules
use `LocalSubnet` rather than a fixed network address.

```bash
# Android emulator (10.0.2.2 is the host machine)
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000

# Physical device on the same Wi-Fi/LAN (automatic discovery)
flutter run

# Release build (HTTPS required)
flutter build apk --dart-define=API_BASE_URL=https://your-domain.example
```

Android emulators can still use `10.0.2.2:8000`. Release APKs require a stable
HTTPS API URL; automatic discovery is intended for local development.
