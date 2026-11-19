# Complete System Documentation

## RGV Multi-Tech Services

Business operations system: service booking and tracking, inventory and
equipment borrowing, staff administration, and an employee mobile app.

**Last reviewed:** 17 November 2026

## Contents

1. Purpose and evidence rules
2. System overview
3. Architecture and runtime boundaries
4. Technology stack
5. Roles and access control
6. Workflows
7. Features and interfaces
8. Data and integrations
9. Security and privacy
10. Project structure and setup
11. Testing and limitations

## 1. Purpose and evidence rules

This document describes the project from source code, migrations,
configuration and tests. It distinguishes:

- **Implemented in source** - code, schema or configuration is present.
- **Tested locally** - a command was run and the result is reported.
- **Not verified** - the repository does not establish the fact.

Backend tests run against in-memory SQLite. Migrations are separately
verified against a real MySQL service in CI (`migrate-check` job) and were
executed against MySQL during this review. No public deployment was queried.

## 2. System overview

### 2.1 Verified scope

- Public pages accept service bookings, show booking tracking, and resolve
  inventory QR tokens.
- The Laravel portal provides distinct administrator and employee experiences.
- Administrators manage bookings, inventory, borrow requests, users, reports,
  notifications, audit logs, backups, imports/exports and settings.
- Employees use the web portal and the Android app for assigned bookings,
  inventory lookup, borrow requests, returns and account settings.
- The report assistant uses local Ollama inference with allow-listed queries.

### 2.2 Limits

The repository does not contain approved objectives, stakeholder interviews or
a live user study. Those remain **not verified**.

## 3. Architecture and runtime boundaries

```text
Browser (SPA at /app) --> Cloudflare Tunnel --> Caddy --/app/*--> React SPA (nginx)
Browser (portal)     -------------------------> Caddy --catch-all--> Laravel 12
Flutter app --HTTPS + Bearer token--> /api/v1/mobile/* --------> Laravel 12
Laravel --private network--> MySQL 8.4 (db-data volume)
Laravel --private network--> Ollama (report assistant)
```

### 3.1 Application paths

- **React SPA** (`Frontend/app`) is served under `/app` and reads and writes
  everything through `/api/v1`. It holds no database credentials.
- **Laravel portal** serves the Blade staff interface and the REST API.
- **Flutter app** (`Frontend/mobile`) calls only `/api/v1/mobile` on Laravel and
  authenticates with a Sanctum bearer token.
- **MySQL** is the single relational store; nothing else connects to it.

### 3.2 Laravel request path

Request -> middleware (security headers, request logging, rate limiting) ->
route guard (session or Sanctum token + role) -> controller -> service ->
Eloquent -> MySQL. Responses are JSON for API routes and Blade views for the
portal.

### 3.3 Local AI path

`OllamaService` calls a private Ollama container. Deterministic record answers
are resolved by `Chatbot/DatabaseAssistant` and formatted by
`Chatbot/AnswerComposer` from users, bookings, borrow requests, borrow items and
scheduled tasks; they never pass through the model. For open-ended questions,
prompts are built from allow-listed aggregates and non-personal reference,
status and date fields; the model never receives SQL or unrestricted records.
Every interaction is recorded in `chatbot_interactions`, and a deterministic
summary is returned when the model is unavailable.

## 4. Technology stack

| Layer | Choice |
|---|---|
| Client (web) | React 18, TypeScript, Vite, React Router, TanStack Query, Tailwind CSS |
| Client (mobile) | Flutter / Dart |
| Backend | Laravel 12 (PHP 8.2+), Sanctum tokens, Spatie permissions |
| Database | MySQL 8.4 (utf8mb4) |
| Integration | Ollama with an open-weight model |
| Reverse proxy | Caddy 2 |
| Containers | Docker Engine + Docker Compose |
| CI/CD | GitHub Actions + self-hosted runner |

## 5. Roles and access control

Two application roles are seeded: **admin** and **employee**. A public visitor
is an unauthenticated actor, not a seeded role.

| Capability | Public | Employee | Admin |
|---|---|---|---|
| Browse public pages, book, track | yes | yes | yes |
| Employee dashboard, own borrow requests | - | yes | yes |
| Create borrow requests, return items | - | yes | yes |
| Administer inventory, users, reports | - | no | yes |
| Audit logs, backups, settings | - | no | yes |

Roles are enforced on the server for every protected route. In the API this
is `EnsureApiRole` plus `auth:sanctum`; in the portal it is the `admin` and
`employee` middleware. The interface only decides which controls to render.

## 6. Workflows

### 6.1 Authentication and account access

An administrator creates an employee at `/admin/users/create`; Laravel hashes
the password and assigns the `employee` role. That account can immediately
sign into the web portal or the mobile app with the same credentials.

The mobile app posts to `/api/v1/mobile/auth/login`. Laravel verifies the
password, checks the account is active and holds the employee role, then
issues a Sanctum token. A password change or deactivation revokes tokens.

### 6.2 Public service booking and tracking

A visitor submits the booking form; the booking is stored and given a
reference number that can be used on the tracking page.

### 6.3 Inventory lifecycle

Administrators create, edit and soft-delete items. Each item receives an
immutable QR token at creation. Low stock is derived from the quantity versus
the per-item threshold.

### 6.4 Borrow, approval and return lifecycle

Employee submits a request (stock is reserved immediately) -> administrator
approves or rejects (rejection releases the reservation) -> administrator or
employee marks items borrowed -> employee returns items with recorded
condition (stock is restored exactly once).

Request creation and return submission are idempotent: a retried submission
with the same key returns the original result instead of duplicating work.

### 6.5 Notifications and audit

Domain events create database notifications per user preference. Audit records
form a checksum chain; writes are serialised with a MySQL named lock so the
chain cannot fork.

### 6.6 Reports, exports and the chatbot

Reports aggregate bookings, inventory, borrowing and users. Exports produce
CSV/XLSX and PDF. The chatbot answers named-person, date-range, return and
pending-task questions from live database rows on the server, and uses
allow-listed aggregates plus non-personal reference fields for open-ended
model questions.

## 7. Features and interfaces

**Web (Laravel portal)** - login, dashboard, bookings, inventory CRUD, borrow
request review, users, reports, notifications, audit logs, backups,
import/export, settings, trash.

**Web (React SPA at `/app`)** - login, dashboard, inventory list with search,
filter, sort and pagination, inventory detail and forms, borrow request list,
detail and creation, profile.

**Mobile (Flutter)** - login, MFA verification, dashboard, inventory search
and QR scan, borrow request creation and history, returns, account settings
and password change.

## 8. Data and integrations

### 8.1 Main data domains

Users and roles; bookings; inventory categories and items; borrow requests,
items and return evidence; notifications; reports; audit logs; import/export
logs; chatbot interactions; and the mobile idempotency and dismissal tables.
The full column-level dictionary is in `Complete Database Schema.md`.

### 8.2 External integrations

Only Ollama. There is no external identity provider, no hosted database, and
no third-party auth service in the runtime path.

## 9. Security and privacy

### 9.1 Implemented measures

- Bcrypt password hashing via Laravel casts.
- Sanctum bearer tokens with ability checks; tokens revoked on password
  change, deactivation and logout.
- Server-side role enforcement on every protected endpoint.
- Server-side validation (FormRequests and inline validation) with a uniform
  JSON error envelope on `/api/v1`.
- CORS restricted to the configured frontend origin.
- Login rate limiting and security headers (CSP, HSTS, frame options).
- Parameterised queries through Eloquent; Blade escaping; no secrets in the
  repository.

### 9.2 Not established

Penetration testing, a production secret rotation drill, and formal OWASP
verification have not been performed.

## 10. Project structure and setup

```text
Backend/        Laravel 12 (API, portal, migrations, tests)
Frontend/app/   React SPA (REST-only client)
Frontend/web/   Vite asset pipeline for the Blade portal
Frontend/mobile/ Flutter employee app
deploy/         Caddyfile and SPA nginx config
compose.yaml    db, app, web, caddy, ollama, optional tunnel
docs/           documentation set, diagrams, evaluation
```

### 10.1 Local setup

```bash
mysql -u root -e "CREATE DATABASE rgv_system CHARACTER SET utf8mb4;"
cd Backend && composer install && php artisan migrate --seed
cd Frontend/app && npm ci && npm run dev
cd Frontend/mobile && flutter pub get
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000
```

### 10.2 Deployment

`docker compose up -d --build` starts the full stack. Every merge to `main`
runs lint and tests, then deploys on the self-hosted runner and verifies the
new commit at `/api/health`. See `deployment.md` and `cicd-runbook.md`.

## 11. Testing and limitations

### 11.1 Verified results

- Backend: **200 tests, 960 assertions** passing on in-memory SQLite.
- SPA: **8 Vitest tests** passing; ESLint clean; production build clean.
- Flutter: **35 tests** passing; analyzer and formatter clean.
- Migrations and seeders executed successfully against MySQL 8.4/MariaDB.

### 11.2 Known limitations

- Line coverage is measured only in CI (no PCOV/Xdebug locally).
- Full-project PHP style has pre-existing debt outside the API modules; see
  `lint-baseline.md`.
- Docker Compose, Caddy routing and the public HTTPS address were not executed
  on the development machine (Docker is not installed there).

### 11.3 Recommended next steps

- Record a backup/restore drill on the deployed database.
- Perform user acceptance testing with at least five target users and report
  SUS and ISO/IEC 25010 results.
- Evaluate the AI integration against a fixed question set with a stated metric.
