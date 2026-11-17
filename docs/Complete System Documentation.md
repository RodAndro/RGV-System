# Complete System Documentation

## RGV Multi-Tech Services

**Document type:** System overview and technical reference  
**Evidence snapshot:** 14 November 2026  
**Repository:** `RodAndro/RGV-System`  
**Status:** Source-grounded documentation; not a certification of a live deployment

---

<div class="page-break"></div>

## Contents

1. [Purpose and evidence rules](#1-purpose-and-evidence-rules)
2. [System overview](#2-system-overview)
3. [Architecture and runtime boundaries](#3-architecture-and-runtime-boundaries)
4. [Technology stack](#4-technology-stack)
5. [Roles and access control](#5-roles-and-access-control)
6. [Workflows](#6-workflows)
7. [Features and interfaces](#7-features-and-interfaces)
8. [Data and integrations](#8-data-and-integrations)
9. [Security and privacy](#9-security-and-privacy)
10. [Project structure and setup](#10-project-structure-and-setup)
11. [Testing, limitations, and recommendations](#11-testing-limitations-and-recommendations)
12. [Evidence references](#12-evidence-references)

<div class="page-break"></div>

## 1. Purpose and evidence rules

This manual describes the project as represented by source code, migrations, configuration, tests, and the documentation present in the workspace on the evidence snapshot date. It distinguishes implementation from operational claims:

- **Implemented in source:** code, schema, or configuration is present.
- **Tested locally:** a test or static-analysis command was run during this documentation task and its result is reported.
- **Configured but not operationally verified:** a deployment or integration definition exists, but no live service was queried.
- **Not verified:** the available repository does not establish the fact.
- **Recommendation:** proposed work, not an implemented capability.

The local PHPUnit configuration explicitly uses an in-memory SQLite database. No live Supabase project, production deployment, external account, actual user data, or GitHub Actions run was accessed. Secret values are intentionally omitted.

The scan covered the Laravel application, route definitions, models, services, Blade views, migrations and seeders; Flutter source and tests; the independent Python mobile service and its SQL/configuration/tests; root manifests, CI and container configuration; and current project documentation. Generated dependencies and build output (`vendor/`, `node_modules/`, Flutter build/cache trees, Python virtual environment, logs, backups, and packaged binaries) were excluded. The sampled `project-highlights/` images are business/premises photographs rather than application UI screenshots; interface descriptions below are based on source templates and widgets.

## 2. System overview

The README identifies the system as the RGV Multi-Tech Services business operations system. Its verified scope includes service booking and tracking, inventory/catalogue management, employee borrowing and returns, administrative account management, reports, notifications, audit history, import/export, backup operations, and a Flutter employee app.

The repository does not provide independently verified business-research findings, approved objectives, stakeholder interviews, or a live-user study. The problem context and formal objectives therefore remain **not verified** rather than inferred from implementation labels.

### 2.1 Verified scope

- Public web pages accept service booking requests, show booking tracking, and resolve inventory QR tokens.
- The authenticated Laravel portal provides administrator and employee experiences.
- Administrators manage bookings, inventory, borrow requests, users, reports, notifications, audit logs, backups, imports/exports, and settings.
- Employees use web and Android workflows for assigned bookings, inventory lookup, borrow requests, returns, notifications, and account settings.
- The Flutter app authenticates with Supabase Auth and uses restricted PostgreSQL functions for mobile business operations.
- A separate FastAPI service and its own container/configuration are also present. The current Flutter source does not call it; this boundary is detailed in §3.3.
- Laravel's reporting chatbot uses local Ollama inference and allow-listed aggregate data retrieval.

### 2.2 Limits of the system description

The source establishes the software features, but not the number of users, actual business volumes, live uptime, hosting performance, hardware sizing, browser compatibility testing, or organizational policy. Those items require operational or stakeholder evidence.

## 3. Architecture and runtime boundaries

### 3.1 Verified application paths

```text
Browser / public visitor
       |
       | HTTPS
       v
Caddy reverse proxy
       |  ordinary paths                     | /api/v1/mobile*
       v                                     v
Laravel 12 / PHP 8.3                  FastAPI mobile-service
       |                                     |
       +---------- TLS PostgreSQL ------------+
                         |
                    Supabase PostgreSQL
                         ^
                         |
Flutter Android -- Supabase Auth + restricted RPCs

Laravel chatbot / insights -- private HTTP --> Ollama
```

The diagram distinguishes configured paths from the native application's observed call path. The Flutter source initializes `supabase_flutter`, signs in through Supabase Auth, and invokes named database RPCs; it does not construct REST requests to Laravel or to `mobile-service`. The standalone service has a separately configured `/api/v1/mobile` REST API and direct PostgreSQL access. The root Caddy configuration routes that path to `mobile-api`, and the deployment workflow starts that service. The repository therefore contains two mobile data-access implementations; their use by a particular deployed APK is not established by this source scan.

The Laravel app connects to the configured PostgreSQL endpoint. Root `compose.yaml` defines Laravel, Ollama, and Caddy; it does not start a PostgreSQL container. `mobile-service/compose.yaml` defines the separate FastAPI container and joins the externally named `rgv-mobile-api` network.

### 3.2 Laravel request path

1. Caddy accepts public HTTP/HTTPS traffic and proxies ordinary web paths to the Laravel Apache container.
2. Laravel routes requests to public, authentication/profile, administrator, employee, or JSON API handlers.
3. Route middleware applies session authentication, role checks, employee MFA, active-account checks, token ability checks, request throttling, and security headers where configured.
4. Controllers invoke Eloquent models and services; business data is read from or written to PostgreSQL.
5. Observers, notifications, audit logging, queued export work, and local Ollama calls are used by relevant workflows.

### 3.3 Independent FastAPI service and architecture discrepancy

`mobile-service/` is an independently deployable FastAPI application. It has health/liveness routes, employee login/MFA, profile/password, dashboard, inventory search/lookup, borrow request, cancel, and return routes. It connects directly to the shared PostgreSQL schema using a server-side database principal with table/column grants. Its deployment definition exposes the listener on loopback and the root Caddy file proxies `/api/v1/mobile*` to it.

However, `mobile/lib/core/network/mobile_api_client.dart`, `mobile/lib/features/auth/data/auth_repository.dart`, and `mobile/README.md` show the current Flutter app using Supabase Auth, RPCs, and Realtime directly. The `mobile-service/README.md` describes the service as the Flutter REST backend, which conflicts with the current client source. The service's production use by an installed app, and whether both mobile backends are intentionally maintained, are **not verified**. Do not treat the two paths as interchangeable without an explicit architecture decision.

### 3.4 Local AI path

The administrator chatbot retrieves allow-listed aggregate report facts through `DatabaseRetrievalService` and sends prompt/context to the configured local Ollama endpoint through `OllamaService`. The repository documentation identifies `qwen3:1.7b` as the default model. No hosted AI fallback is configured in the inspected service. This does not establish model quality or runtime availability.

## 4. Technology stack

| Technology | Evidence and purpose |
|---|---|
| PHP 8.2+ / Laravel 12 | Backend, routing, session authentication, controllers, Eloquent persistence, validation, jobs, notifications, and Blade web UI; `composer.json`, `routes/`, `app/`. |
| PostgreSQL / Supabase | Canonical relational store described by the current README and deployment configuration. Production connectivity and schema apply status were not queried. |
| Blade, Tailwind CSS, Alpine.js, Vite | Server-rendered browser interface and asset build; `resources/views/`, `resources/css/`, `resources/js/`, `package.json`. |
| Laravel Sanctum and Spatie Permission | Laravel token endpoints and role/permission data; `composer.json`, API routes, and `database/seeders/RoleAndPermissionSeeder.php`. |
| Supabase Auth, PostgreSQL functions and Realtime | Native Flutter sign-in, MFA, RPC workflows, and filtered borrow-request updates; `mobile/pubspec.yaml`, `mobile/lib/`, `database/supabase/mobile_backend.sql`. |
| Flutter / Dart | Android employee application; SDK constraint is recorded in `mobile/pubspec.yaml`. |
| FastAPI, Uvicorn, psycopg, bcrypt | Separate Python REST service, SQL access, and password verification; `mobile-service/requirements.txt`, `mobile-service/app/`. |
| Ollama | Local language-model inference for the Laravel chatbot and related insight features; `app/Services/OllamaService.php`, `config/services.php`. |
| Docker Compose, Apache, Caddy | Container deployment and HTTPS reverse-proxy topology; `Dockerfile`, `compose.yaml`, `deploy/Caddyfile`. |
| PHPUnit, Flutter Test, pytest | Backend, mobile widget/model, and mobile-service tests. |
| Dompdf, PhpSpreadsheet, QR code library | PDF output, spreadsheet import/export, and QR generation; direct dependencies in `composer.json`. |

These are direct dependencies or configured services, not claims that each external service is currently running. Dependency names and versions should be taken from `composer.json`, `package.json`, `mobile/pubspec.yaml`, and `mobile-service/requirements.txt`; the exact deployed lockfile/image revisions were not checked.

## 5. Roles and access control

The seeded application roles are **admin** and **employee**. A public visitor is an unauthenticated actor, not a seeded application role. The `rgv_mobile_api` PostgreSQL login configured for the separate service is a database principal, not a Laravel user role.

- Laravel administrator routes use `auth` plus the `admin` middleware.
- Laravel employee routes use `auth`, `employee`, and `mfa` middleware.
- Laravel mobile REST routes use Sanctum, active-employee checks, employee token ability, and throttling.
- The direct Flutter path checks that the authenticated Supabase Auth identity is linked to an active employee row and employee role. Restricted functions enforce MFA assurance for protected operations.
- The role seeder grants all seeded permissions to administrators and a narrower set to employees. Several route groups use role middleware rather than checking each seeded permission independently; role-permission seed data should not be read as proof that every individual controller action has a `can` check.

The complete module/action matrix is in [System Features and Role Permissions](System%20Features%20and%20Role%20Permissions.md).

## 6. Workflows

### 6.1 Authentication and account access

The web app uses Laravel authentication, session routes, password reset/confirmation, email verification, and MFA views. The dashboard redirect selects the administrator or employee dashboard according to the authenticated role. The employee web group applies MFA middleware when an account has MFA enabled.

The Android app initializes Supabase using public URL/key configuration, signs in with email/password through Supabase Auth, checks the employee profile through a restricted RPC, completes TOTP MFA when required, and restores or clears the local Auth session based on the profile result. Business transactions require a reachable Supabase service; the app does not present queued/offline saves.

### 6.2 Public service booking and tracking

1. A visitor opens the public booking form and submits booking data.
2. Laravel validates and stores the booking, assigns a reference number, and uses booking status and optional employee assignment for later processing.
3. An administrator reviews the request and can approve, reject, complete, cancel, assign, or delete it through the admin booking routes.
4. A visitor can search/track by booking reference.

The route/controller/view path is defined by `routes/web.php`, `PublicController`, `Admin/BookingController`, `Booking`, and `resources/views/public/`. A live customer workflow was not exercised during documentation.

### 6.3 Inventory lifecycle

Administrators create and maintain categorized inventory, change stock/status, inspect low-stock lists, and use QR lookup/generation. The QR token is stored on the inventory record and the public QR route constrains token shape. Employees have read-only inventory list/detail and low-stock views. Borrow/return processing changes stock through the borrowing service or corresponding mobile backend.

### 6.4 Borrow, approval, and return lifecycle

1. An employee submits one or more items with dates and a reason.
2. `BorrowService` normalizes the request, locks inventory rows in a transaction, checks active/available status and quantity, creates a pending request and item rows, reserves/decrements stock, then notifies administrators after commit.
3. An administrator approves or rejects the request. Rejection releases reserved stock; an approved request can be marked borrowed.
4. A return records item condition and location. The service restores stock only for items not previously returned and marks the request returned when all items are complete.
5. The mobile RPC and standalone service implementations also persist idempotency results for retry-safe borrow/return transactions.

The local Laravel tests cover stock reservation, competing requests, rejection/cancellation release, and return idempotency. The Python PostgreSQL integration tests were skipped in this task because a disposable `TEST_DATABASE_URL` was not configured.

### 6.5 Notifications and audit

Domain observers/services and the mobile API write notification records for selected events. Notification preferences distinguish in-app and email delivery. Audit records include actor/event/subject fields and a chained checksum; relevant audit changes are tested locally. Actual SMTP delivery and production audit retention were not tested.

### 6.6 Reports, exports, and chatbot

Administrators can view booking, inventory, borrow-request, and user reports. Routes expose PDF exports and import/export operations; jobs and log tables track processing state. The chatbot is administrator-only and rate-limited; retrieval uses aggregate counts and values rather than unrestricted user or booking records. Its interaction table stores a question hash, page type, source references, response, model, outcome, and elapsed time rather than raw question text.

## 7. Features and interfaces

The main web page groups are public, authentication/profile, administrator, and employee templates. Administrator pages include dashboards, bookings/calendar, borrow requests, inventory, users/analytics, reports, import/export, notifications, audit logs, backups, settings, trash, system health, and AI insights. Employee pages include dashboard, assigned bookings, inventory, borrow requests, notifications, and profile/settings.

The Flutter app source defines login and MFA gates, dashboard, inventory search/QR scan, borrow request start/form/history, return, account settings, and password change. It also contains shared loading/error states, request/status widgets, and real-time request refresh subscriptions.

There are no verified in-browser screenshots in the scanned evidence. The PDF deliverables therefore do not invent screenshot captions or portray the marketing photographs as UI screenshots.

## 8. Data and integrations

### 8.1 Main data domains

The migration set defines 38 relational tables, including user/role/auth support, bookings, inventory categories and items, borrow requests/items/return evidence, notifications/preferences, reports, import/export logs, audit/activity logs, backup monitoring, settings, rate limiting, mobile idempotency/dismissal, chatbot interaction records, data-migration mappings, and Laravel cache/session/queue support.

The complete migration-derived column inventory, relationships, defaults, uniqueness, and caveats are in [Complete Database Schema](Complete%20Database%20Schema.md).

### 8.2 External integrations

- **Supabase Auth:** Flutter authentication and MFA; Laravel's `SupabaseAuthAdminService` provisions/updates employee Auth identities using a server-only key.
- **Supabase PostgreSQL:** shared relational storage and direct Flutter RPCs. The repository does not establish that production SQL has been applied.
- **Ollama:** local language model endpoint configured for Laravel. Model fetch/inference was not run.
- **Email:** Laravel notifications and optional standalone-service SMTP configuration. Delivery was not tested.
- **Docker/Caddy/GitHub Actions:** deployment configuration exists; public TLS, runner registration, workflow success, and external GitHub settings remain unverified.

## 9. Security and privacy

### 9.1 Verified code/configuration measures

- Laravel password hashing and hidden serialization for password, remember token, and Supabase Auth identifier.
- Role-gated web routes; active employee, token ability, ownership, throttling, and MFA checks on applicable mobile routes.
- Global security-header and HTTPS middleware registration.
- Supabase mobile functions are `SECURITY DEFINER` with a fixed `search_path`, check the Auth identity against an active employee/role, and require elevated MFA assurance where configured. Underlying tables revoke broad client access; the SQL grants authenticated execution only to the reviewed mobile RPCs and defines an owner-filtered Realtime read policy.
- Laravel-side Supabase Auth administration uses environment configuration and checks that database and Auth project references match when those values are parseable.
- The separate FastAPI service verifies Laravel-compatible bcrypt hashes, enforces employee role/status, issues hashed Sanctum-compatible token secrets, uses parameterized SQL, has throttling and validation, and is configured with a restricted database login.
- Chatbot retrieval is allow-listed and aggregate-only in the inspected service; no SQL from a model is executed.
- The API logging and exception handlers avoid returning SQL/credentials in error responses. Full production log behavior was not verified.

### 9.2 Not established

Presence of these controls does not certify the deployed system. No independent penetration test, OWASP review, database permission audit, TLS scan, backup restore, retention review, accessibility review, or live-secret audit was conducted. Actual RLS/grants on a Supabase instance and the security of a deployed APK require environment access.

### 9.3 Recommendations

1. Decide and document whether the Flutter app should use Supabase RPCs or the separate FastAPI API; remove or clearly label the unused path only after confirming deployment needs.
2. Keep server secrets, database credentials, and audit HMAC material in a secret manager; never include them in mobile build defines or PDFs.
3. Apply and verify least-privilege database grants/RLS in a non-production project before release.
4. Run and record an external security review, accessibility audit, production backup/restore drill, and privacy/retention review.

## 10. Project structure and setup

| Path | Purpose |
|---|---|
| `app/` | Laravel controllers, middleware, models, jobs, notifications, observers, and services. |
| `routes/` | Browser and JSON route definitions. |
| `resources/views/`, `resources/css/`, `resources/js/` | Blade pages, CSS, and frontend assets. |
| `database/migrations/`, `database/seeders/` | Relational schema evolution and guarded development/test seeders. |
| `database/supabase/mobile_backend.sql` | Supplemental Supabase Auth/RPC/RLS setup for the current direct-Supabase Flutter client. |
| `mobile/` | Flutter Android application and tests. |
| `mobile-service/` | Separate FastAPI API, SQL grants/setup, tests, and Compose service definition. |
| `docs/` | Current architecture, deployment, migration, CI/CD, traceability, and these generated documentation sources. |
| `deploy/`, `Dockerfile`, `compose.yaml` | Caddy configuration and main deployment stack. |

### 10.1 Local Laravel setup

The README documents PHP 8.2+, Composer, Node.js 24+, and a Supabase PostgreSQL project:

1. Install PHP dependencies with `composer install`.
2. Copy `.env.example` to `.env`, generate a Laravel app key, and configure database values from the Supabase dashboard. Require TLS and keep passwords in the untracked environment/secret store.
3. Run additive migrations with `php artisan migrate`. Do not reset a database using destructive migration commands.
4. Run `npm.cmd ci` and `npm.cmd run build` for web assets.
5. Use `composer run dev` for local Laravel/Vite development.

These are repository instructions, not a deployment performed during this documentation task. Mail and Ollama may be optional in development; their behavior depends on environment configuration.

### 10.2 Flutter and service setup

The native app uses the public Supabase URL and publishable key as build-time defines; it must not receive the Supabase secret or a PostgreSQL connection string. Apply the documented `database/supabase/mobile_backend.sql` only after reviewing the target project and migration state. `mobile/README.md` has the app build commands.

The independent Python service uses Python 3.12 in Docker/CI and pinned requirements. Its separate README describes a dedicated PostgreSQL principal and TLS. No live database setup or deployment was performed here.

### 10.3 Deployment

Main Compose services are Laravel, Caddy, and Ollama; the separate mobile service is started using its own Compose file by the CI deployment workflow. Supabase PostgreSQL is external to these Compose services. Caddy alone publishes ports 80/443 in the root Compose file. `docs/deployment.md` and `docs/cicd-runbook.md` contain operator procedures; none of the public URL, GitHub runner, DNS, production migration, or restore steps were verified here.

## 11. Testing, limitations, and recommendations

### 11.1 Commands run for this documentation snapshot

| Area | Result | Scope/caveat |
|---|---|---|
| Laravel/PHPUnit | **148 passed, 689 assertions** | `php artisan test`; configured in-memory SQLite, not production PostgreSQL. |
| Flutter tests | **32 passed** | `cd mobile; flutter test`. |
| Flutter static analysis | **No issues found** | `cd mobile; flutter analyze`. |
| FastAPI pytest | **8 passed, 8 skipped, 1 warning** | Unit/runtime tests passed; PostgreSQL integration tests were skipped because `TEST_DATABASE_URL` was not set. |
| Web asset build | **Not run** | No result is claimed. |
| GitHub Actions / deploy | **Not run or verified** | GitHub settings, production runner, and live service were not accessed. |
| Live database / restore / AI evaluation | **Not run** | No production or target database or Ollama model was queried. |

### 11.2 Known limitations and evidence gaps

- The Flutter-to-FastAPI integration described in `mobile-service/README.md` is not present in the current Flutter client source; the client calls Supabase directly.
- The service path is nevertheless present in Caddy and deployment configuration. Its relationship to the current native app needs an explicit owner decision and end-to-end verification.
- `docs/openapi.yaml` documents the Laravel API, not a complete OpenAPI contract for every FastAPI route or for the direct Supabase RPC interface.
- The repository includes documentation and deployment history with differing mobile architecture descriptions. Current runtime conclusions here are based on current client/source/config files, not historical narrative text.
- The README retains links to legacy system/mobile API references that are absent from this working tree. This deliverable set adds current system, schema, and permissions documents; it does not recreate those API references.
- The production PostgreSQL schema, Supabase grants/RLS, actual DNS/TLS, CI branch protection, public application behavior, and external user experience remain unverified.
- No complete UI screenshot set, browser compatibility report, hardware sizing, user acceptance study, or model evaluation results were found in the inspected evidence.

### 11.3 Recommended next steps

1. Resolve the mobile-backend architecture discrepancy and align the client, proxy, OpenAPI documentation, deployment, and operator instructions.
2. Run the Python PostgreSQL integration suite against a disposable database created from the current migrations.
3. Verify additive SQL setup and grants in a non-production Supabase project, then record migration/version evidence.
4. Perform a protected backup and isolated restore drill; capture database and file-storage checks.
5. Run the documented Ollama evaluation set and publish measured grounding/quality/latency results rather than inferred claims.
6. Capture privacy-safe, current-version screenshots and conduct accessibility/responsive testing.
7. Obtain actual production CI, deployment, HTTPS, and access-control evidence before describing the system as operational.

## 12. Evidence references

The following source files are the primary evidence for this manual:

- System and setup: `README.md`, `composer.json`, `package.json`, `mobile/pubspec.yaml`, `mobile/README.md`.
- Routes and access: `routes/web.php`, `routes/api.php`, `routes/auth.php`, `bootstrap/app.php`, `app/Http/Middleware/IsAdmin.php`, `IsEmployee.php`, `EnsureActiveEmployee.php`, `RequireMfa.php`, `RequireTokenAbility.php`.
- Domain workflows: `app/Services/BorrowService.php`, `app/Http/Controllers/PublicController.php`, `app/Http/Controllers/Admin/`, `app/Http/Controllers/Employee/`, `app/Models/`.
- Mobile: `mobile/lib/main.dart`, `mobile/lib/core/network/mobile_api_client.dart`, `mobile/lib/features/auth/data/auth_repository.dart`, repository and screen folders under `mobile/lib/features/`.
- Independent API service: `mobile-service/app/main.py`, `mobile-service/app/security.py`, `mobile-service/app/database.py`, `mobile-service/sql/`, `mobile-service/README.md`.
- Data and roles: `database/migrations/`, `database/seeders/RoleAndPermissionSeeder.php`, `database/supabase/mobile_backend.sql`.
- AI and audit: `app/Services/DatabaseRetrievalService.php`, `OllamaService.php`, `app/Http/Controllers/ChatbotController.php`, `database/migrations/2026_11_11_000001_create_chatbot_interactions_table.php`.
- Deployment: `Dockerfile`, `compose.yaml`, `deploy/Caddyfile`, `mobile-service/compose.yaml`, `.github/workflows/ci-cd.yml`, `docs/deployment.md`, `docs/cicd-runbook.md`.
- Test configuration/results: `phpunit.xml`, `tests/`, `mobile/test/`, `mobile-service/tests/`.

The accompanying [Complete Database Schema](Complete%20Database%20Schema.md) and [System Features and Role Permissions](System%20Features%20and%20Role%20Permissions.md) are companion appendices. They share the same evidence snapshot and limitations.
