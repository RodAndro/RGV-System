# System Features and Role Permissions

**Evidence snapshot:** 14 November 2026  
**Project:** RGV Multi-Tech Services  
**Status:** Source-grounded inventory; deployment behavior is not certified

---

<div class="page-break"></div>

## Contents

1. [How to read this document](#1-how-to-read-this-document)
2. [Role and module matrix](#2-role-and-module-matrix)
3. [Feature inventory](#3-feature-inventory)
4. [Data lifecycle and integration notes](#4-data-lifecycle-and-integration-notes)
5. [Error and empty-state evidence](#5-error-and-empty-state-evidence)
6. [Tests and verification](#6-tests-and-verification)
7. [Source evidence index](#7-source-evidence-index)

<div class="page-break"></div>

## 1. How to read this document

- **In source** means routes, controllers, UI, services, or configuration were found.
- **Tested** means the relevant test suite was run during this task; it does not mean every feature has an end-to-end test.
- **Not verified** means the repository alone does not establish operational behavior or a permission.
- A route being hidden in navigation is not proof of backend authorization. Route middleware and ownership checks are described separately from seeded permissions.

The application roles seeded in `database/seeders/RoleAndPermissionSeeder.php` are `admin` and `employee`. Public visitors use unauthenticated routes and are not an application role. The FastAPI database account is an infrastructure principal, not a user role.

## 2. Role and module matrix

`Yes` means an applicable route/source path is present. `Own/assigned` means an employee operation is limited to the employee's records or assigned work. `No route found` is limited to the inspected route definitions; it is not a statement about external systems. `N/V` means a specific permission could not be established.

| Module/action | Public visitor | Administrator | Employee |
|---|---|---|---|
| Public home / public inventory QR lookup | View public pages and QR result | View | View QR result; authenticated inventory catalogue also available |
| Submit service booking | Create booking | Review/manage submitted requests; no admin create route found | No create route found; view assigned bookings |
| Track booking | Search/view by reference | View and manage all routed booking records | View assigned booking details |
| Booking create/edit/delete | Create public booking only | Admin booking routes include assignment, approval, rejection, completion, cancellation, and delete | No edit/delete route found |
| Inventory catalogue | Public QR lookup only | View/create/edit/delete; import and export; low-stock and QR actions | View/detail/low-stock; no inventory write route found |
| Borrow requests | No route found | View, approve, reject, mark borrowed, return, delete | Web: create/view own, mark borrowed, return, and delete returned/rejected requests. Mobile: create/view, cancel own pending request, and return own items. |
| User/account administration | None | View/create/edit/delete users, status toggle, force logout, impersonate, analytics | Own profile, password, MFA, personal exports, and notification preferences |
| Reports and AI insights | None | Booking/inventory/borrow/user reports, AI insight routes, administrator chatbot, PDF/spreadsheet exports | No admin report route; personal data/order-history exports are present in profile routes |
| Notifications | None | View and manage administrator notifications | View/manage employee notifications |
| Audit history | None | View/export/clear audit logs | No route found |
| Backup/restore | None | Run/export/import/verify/download backups and update backup settings | No route found |
| System settings/operations | None | Settings, trash restore/permanent delete, system-health page, import/export | Own account settings only |
| Mobile API | None | Not a mobile business client role; administrator mobile login is rejected in service code | Active employee plus authenticated token/session; own data only |

### 2.1 Enforced access boundaries

| Surface | Evidence-based enforcement |
|---|---|
| Admin web routes | `auth` + `admin` middleware. `IsAdmin` checks the `admin` role and returns 403 otherwise. |
| Employee web routes | `auth` + `employee` + `mfa` middleware. `IsEmployee` checks the employee role. `RequireMfa` blocks employee routes when MFA is enabled but not verified in the session. |
| Laravel employee REST routes | Sanctum authentication, active employee check, `employee` token ability, and throttling. Controllers/services perform ownership and state checks for relevant operations. |
| Direct Flutter/Supabase path | Supabase Auth session plus restricted RPCs. SQL resolves the linked active employee and role and checks MFA assurance for protected operations. Realtime read access to borrow requests is filtered to the authenticated employee. |
| Standalone FastAPI path | Bearer token digest lookup, token ability, active employee and employee-role checks, record ownership, input validation, and rate limits. |
| Public routes | Public booking, tracking, and inventory QR handlers are outside the authenticated admin/employee groups. The particular data returned is determined by their controller implementations. |

### 2.2 Seeded permission names

The permission seeder defines the following names:

- **Bookings:** `view bookings`, `create bookings`, `edit bookings`, `delete bookings`, `approve bookings`, `reject bookings`, `export bookings`.
- **Inventory:** `view inventories`, `create inventories`, `edit inventories`, `delete inventories`, `manage inventory stock`, `export inventories`.
- **Borrow requests:** `view borrow requests`, `create borrow requests`, `approve borrow requests`, `reject borrow requests`, `return borrowed items`.
- **Users:** `view users`, `create users`, `edit users`, `delete users`, `manage roles`.
- **Reports:** `view reports`, `generate reports`, `export reports`, `ai analytics`.
- **System:** `view activity logs`, `manage settings`, `manage backups`, `manage imports`, `manage exports`, `view audit logs`, `manage api security`.

The seeder grants all these permissions to `admin`; `employee` receives `view bookings`, `view inventories`, `create borrow requests`, `view borrow requests`, and `return borrowed items`. These assignments do not prove that every route invokes the permission package's per-permission middleware: the inspected route groups primarily use role middleware. Controller-level checks and ownership rules still apply.

## 3. Feature inventory

### 3.1 Public web

| Feature | Entry point | Inputs / outputs and processing | Status and evidence |
|---|---|---|---|
| Landing and service information | `GET /` | Public landing page and navigation. | In source: `PublicController`, `resources/views/public/welcome.blade.php`. Live UI not verified. |
| Service booking | `GET/POST /booking` | Form data is submitted to the public controller and stored as a booking with a reference/status lifecycle. | In source: `routes/web.php`, `PublicController`, `Booking`, `resources/views/public/booking.blade.php`; selected booking behavior appears in Laravel tests. |
| Booking tracking/search | `/booking/track/{reference}`, `POST /booking/search` | Reference-based lookup and status display. | In source: `PublicController`, `resources/views/public/track.blade.php`; production data not queried. |
| Public QR inventory lookup | `GET /inventory/qr/{token}` | Route constrains token shape to `QR-` plus 32 uppercase alphanumeric characters; controller resolves the inventory token. | In source and tested for invalid token/admin QR cases: `PublicController`, `InventoryQrTest.php`. |

### 3.2 Administrator web portal

| Feature | Route/page family | Main operations | Evidence |
|---|---|---|---|
| Dashboard and metrics | `/admin/dashboard`, `/admin/dashboard/stats` | Summary metrics and dashboard polling. | `Admin/DashboardController.php`, `resources/views/admin/dashboard.blade.php`. |
| Booking operations | `/admin/bookings/*` | List/calendar/detail; approve, reject, complete, cancel, assign employee, delete. | `Admin/BookingController.php`, `resources/views/admin/bookings/`. |
| Inventory management | `/admin/inventories/*` | List/create/detail/edit/delete, low-stock view, QR code, import and template. | `Admin/InventoryController.php`, `resources/views/admin/inventories/`. |
| Borrow review | `/admin/borrow-requests/*` | Review requests, approve/reject, mark borrowed, process returns, and delete/trash operations. | `Admin/BorrowRequestController.php`, `resources/views/admin/borrow-requests/`. |
| User administration | `/admin/users/*` | User list/detail/create/edit/delete, analytics, status toggling, impersonation, force logout. | `Admin/UserController.php`, `Admin/ImpersonationController.php`, `resources/views/admin/users/`. |
| Reports | `/admin/reports/*` | Booking, inventory, borrow-request, and user reports. | `Admin/ReportController.php`, `resources/views/admin/reports/`. |
| Exports and invoices | `/admin/export/*`, `/admin/import-export/*`, booking invoice | PDF exports, spreadsheet import/export, status/error downloads, customer invoice route. | `PdfExportController.php`, `Admin/ImportExportController.php`, `GenerateExport` job, `resources/views/pdf/`. |
| Notifications | `/admin/notifications/*` | Inbox, unread count, open, mark read, delete, clear. | `Admin/NotificationController.php`, `resources/views/admin/notifications/`. |
| Audit | `/admin/audit-logs/*` | List/detail/export/clear audit entries. | `Admin/AuditLogController.php`, `AuditLogger.php`, audit views. |
| Backups | `/admin/backups/*` | Run, export/import, verify, download, clear, settings. | `Admin/BackupController.php`, `DatabaseBackupRestoreService.php`, backup views. |
| Settings and records recovery | `/admin/settings`, `/admin/trash/*` | Settings and restore/permanent-delete operations for supported trashed records. | `Admin/SettingsController.php`, `Admin/TrashController.php`. |
| System status | `/admin/system-health` | Renders an administrative system-health page. | `routes/web.php`, `resources/views/admin/system-health.blade.php`; live health is separately exposed at `/api/health`. |
| AI insights | `/admin/ai/*` | Insights, booking forecast, inventory recommendations. | `AiReportController.php`, `OllamaService.php`; model quality not evaluated. |
| Aggregate chatbot | `POST /api/chatbot/query` | Admin-only, rate-limited query over allow-listed aggregate facts; response and interaction metadata are recorded. | `ChatbotController.php`, `DatabaseRetrievalService.php`, `chatbot_interactions` migration, `ChatbotTest.php`. |

### 3.3 Employee web portal

| Feature | Route/page family | Main operations | Evidence |
|---|---|---|---|
| Employee dashboard | `/employee/dashboard` | Employee overview and request indicators. | `Employee/DashboardController.php`, employee dashboard Blade view. |
| Assigned work | `/employee/bookings/*` | View booking list and details assigned to the employee. | `Employee/BookingController.php`, `resources/views/employee/bookings/`. |
| Borrow requests | `/employee/borrow-requests/*` | Create/list/detail; mark borrowed and return; delete is limited to returned/rejected requests and is soft-delete/restorable by an admin. | `Employee/BorrowRequestController.php`, borrow views, `BorrowService.php`. |
| Inventory | `/employee/inventories/*` | Read-only list/detail and low-stock view. | `Employee/InventoryController.php`, employee inventory views. |
| Notifications | `/employee/notifications/*` | Inbox, unread count, open, mark read, delete, clear. | `Employee/NotificationController.php`, employee notification view. |
| Profile and preferences | `/profile/*` | Profile/password, MFA, notification preferences, personal-data and order-history exports. | `ProfileController.php`, `routes/web.php`, `resources/views/profile/`. |

### 3.4 Flutter Android app

| Screen/workflow | Verified source behavior | Evidence |
|---|---|---|
| Startup/login | Initializes Supabase, validates HTTPS project URL, signs in with Supabase Auth, restores session. | `mobile/lib/main.dart`, `core/config/app_config.dart`, `features/auth/`. |
| MFA | Uses Supabase Auth TOTP factor challenge/verification and shows setup/verification screens as required. | `AuthRepository`, `mfa_screen.dart`. |
| Dashboard | Reads employee summary/recent requests and can dismiss a recent request through an RPC. | `DashboardRepository`, `dashboard_screen.dart`; `mobile_backend.sql`. |
| Inventory search/QR | Searches and looks up inventory through RPCs; normalizes plain code, JSON payload, or QR URL. | `InventoryRepository`, `qr_scan_screen.dart`, `mobile_api_client.dart`. |
| Borrow | Creates an idempotent borrow request, displays history, cancels eligible own request, and subscribes to request changes. | `BorrowRepository`, borrow screens, `mobile_backend.sql`. |
| Return | Lists returnable items, submits location/condition/damage notes with an idempotency key, refreshes through Realtime. | `ReturnRepository`, `return_screen.dart`, SQL RPC. |
| Account | Updates employee name and password using current app integrations. | `AuthRepository`, account screens. |
| Online/error behavior | Business data is not cached or queued as saved; Supabase failures surface as app errors. | `mobile/README.md`, shared async/error widgets, tests. |

The mobile app currently uses Supabase directly; it does not call the FastAPI service based on the inspected client source. The UI was not run on a device for this document.

### 3.5 JSON APIs and database RPCs

**Laravel API routes** in `routes/api.php` include:

- `GET /api/health` and `GET /api/docs`.
- Public `GET /api/inventories`.
- A Sanctum mobile REST set under `/api/v1/mobile`: login, MFA verification, logout, profile, dashboard, inventory search/lookup, borrow request list/create/detail/returnable/cancel/return, profile update, and password change.

The Laravel OpenAPI file is `docs/openapi.yaml`. It is not a complete contract for the independent FastAPI service or direct Supabase RPCs; maintainers should compare it to current route definitions before relying on endpoint parity.

**Direct Supabase mobile RPCs** referenced in the Flutter repository include `mobile_employee_profile`, `mobile_inventory_lookup`, `mobile_inventory_search`, `mobile_list_borrow_requests`, `mobile_dashboard`, `mobile_dismiss_recent_request`, `mobile_create_borrow_request`, `mobile_cancel_borrow_request`, `mobile_returnable_items`, `mobile_return_borrow_items`, and `mobile_update_employee_name`. Auth sign-in, MFA, sign-out, and password changes use Supabase Auth operations.

**FastAPI service** provides `/health`, `/live`, `/api/v1/mobile/health`, and employee REST routes for auth/MFA/logout, current profile, dashboard, inventory lookup/search, borrow request list/detail/create/cancel/returnable/return, profile, and password. Its code and tests are under `mobile-service/`. These are implemented but not demonstrated as the current Flutter call path.

## 4. Data lifecycle and integration notes

- Booking and inventory CRUD persist through Laravel models and migrations.
- Borrow request creation reserves inventory within a transaction; approvals, rejection/cancellation, borrowing, and return update request/item/inventory rows.
- Notification and audit records are generated for selected business events.
- Export jobs and import/export log tables record processing status and file metadata.
- The direct Flutter functions use `SECURITY DEFINER` RPCs that authenticate the caller's linked active employee, enforce ownership and state, and persist idempotency data for mobile writes.
- The independent FastAPI service independently implements much of the same mobile workflow over shared PostgreSQL tables. Its database schema and grants are documented in `mobile-service/sql/`.
- The chatbot receives aggregate facts only from its allow-list; interaction telemetry stores a hash/reference rather than raw user question text.

Database table/column/key details are in [Complete Database Schema](Complete%20Database%20Schema.md).

## 5. Error and empty-state evidence

Laravel routes and controllers use framework validation, domain exceptions, JSON error responses, and Blade views. The Flutter project has shared asynchronous/loading/error-state widgets and maps Supabase failures to app exceptions. FastAPI returns validation and API error JSON and suppresses unexpected exception details from the response. The repository tests exercise selected failure cases (invalid login, inactive/admin mobile accounts, unavailable inventory, required return location, duplicate/idempotent requests, and offline model behavior); they do not prove uniform error shapes across every route.

## 6. Tests and verification

The following commands were run for this snapshot:

| Test/check | Result |
|---|---|
| `php artisan test` | 148 passed, 689 assertions; in-memory SQLite. |
| `cd mobile; flutter test` | 32 passed. |
| `cd mobile; flutter analyze` | No issues found. |
| `cd mobile-service; .venv\Scripts\python.exe -m pytest -q` | 8 passed, 8 skipped, 1 dependency deprecation warning. The PostgreSQL integration tests were skipped without `TEST_DATABASE_URL`. |

No CI run, device/browser E2E run, live PostgreSQL test, public deployment check, or production permission test was performed.

## 7. Source evidence index

- Routes: `routes/web.php`, `routes/api.php`, `routes/auth.php`.
- Middleware and role setup: `bootstrap/app.php`, `app/Http/Middleware/`, `database/seeders/RoleAndPermissionSeeder.php`.
- UI: `resources/views/public/`, `resources/views/admin/`, `resources/views/employee/`, `resources/views/profile/`, `mobile/lib/features/`.
- Domain and APIs: `app/Http/Controllers/`, `app/Services/BorrowService.php`, `mobile/lib/core/network/mobile_api_client.dart`, `database/supabase/mobile_backend.sql`, `mobile-service/app/main.py`.
- Tests: `tests/Feature/`, `tests/Unit/`, `mobile/test/`, `mobile-service/tests/`.

Companion documents: [Complete System Documentation](Complete%20System%20Documentation.md) and [Complete Database Schema](Complete%20Database%20Schema.md).
