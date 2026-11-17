# RGV System

RGV Multi-Tech Services business operations system: Laravel 12 web/API application and Flutter Android employee client.

## Existing features

- Public booking, tracking, and inventory QR lookup.
- Admin and employee portals for bookings, inventory, borrowing, reports, notifications, users, audit history, backups, and import/export.
- Flutter employee workflows for login/MFA, dashboard, inventory search and QR lookup, borrow requests, returns, and account settings.
- PostgreSQL as the relational source of truth; a separately deployed mobile API serves Flutter, while Laravel serves the web application.
- Local Ollama inference for report insights and the admin chatbot. Chatbot report retrieval uses allow-listed aggregate queries and returns source references. No hosted AI fallback is used.

## Requirements

- PHP 8.2+, Composer, a Supabase PostgreSQL project, Node.js 24+, and Flutter/Dart for Android work.
- Docker Compose v2 for the container deployment.

## Local development

1. Install PHP and JavaScript dependencies:

   ```powershell
   composer install
   Copy-Item .env.example .env
   php artisan key:generate
   ```

2. Set the Supabase PostgreSQL connection variables in `.env` from the project Dashboard's Connect panel. The example uses the shared Session pooler for IPv4 compatibility; use the direct endpoint only on IPv6-capable hosts. Require TLS (`DB_SSLMODE=require`) and keep the database password in the untracked `.env` or deployment secret store. Then run additive migrations:

   ```powershell
   php artisan migrate
   ```

   Migrations do not reset or seed existing data. Do not use `migrate:fresh` or `db:wipe` on an existing database.
   To populate local/test sample categories and inventory, run `php artisan db:seed`. Demo accounts are optional and are created only when `DEMO_*_EMAIL` and `DEMO_*_PASSWORD` are set in local `.env`; the sample seeder refuses to run in production.

3. Install frontend dependencies and build assets:

   ```powershell
   npm.cmd ci
   npm.cmd run build
   ```

4. Start Laravel and Vite for local work:

   ```powershell
   composer run dev
   ```

Mail uses Laravel's local log driver by default. Set up SMTP only when outbound email delivery is needed. Ollama is optional during development; if unavailable, the chatbot reports that local generation is offline and deterministic report summaries remain available.

## Docker deployment

Copy `.env.example` to `.env`, set a unique `APP_KEY` and strong `DB_PASSWORD`, and set `APP_DOMAIN` to the DNS name that points to this host. Use the Session Pooler host and username from Supabase Dashboard > Connect if this host is IPv4-only. Then:

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec ollama ollama pull qwen3:1.7b
```

Caddy is the only service publishing ports 80/443. Laravel connects to the configured Supabase PostgreSQL endpoint over TLS; no second application database is started. Uploaded/application files, Ollama model files, logs, and Caddy certificates use named volumes. To use Cloudflare Tunnel, route the hostname to the Caddy service or host port; keep the tunnel credential outside the repository.

Deployment, rollback, backup, and restore procedures are in [docs/deployment.md](docs/deployment.md).

## CI/CD

`.github/workflows/ci-cd.yml` runs backend tests/coverage, the web asset build, Flutter checks, and mobile-service tests against disposable PostgreSQL on pull requests to `main`, pushes to `main`, and manual workflow dispatches. Only a successful push to `main` can deploy. Production runner, GitHub environment, branch rules, and pipeline verification steps are documented in [docs/cicd-runbook.md](docs/cicd-runbook.md).

## API

- Health: `GET /api/health` (database availability, version, and commit).
- OpenAPI YAML: `GET /api/docs`.
- Web API: Laravel health and documentation endpoints above. The Flutter app does not call Laravel or the optional legacy `mobile-service`.
- The admin chatbot remains available at `POST /api/chatbot/query` through the authenticated web session and CSRF protection.

## Flutter client

The mobile app connects directly to the shared Supabase project using Supabase Auth and restricted database RPCs. It reads current data and submits each transaction to Supabase online; it does not cache business records or queue writes. If Supabase is unreachable, the operation fails visibly and is not reported as saved. Laravel does not need to be running for native workflows. Apply [database/supabase/mobile_backend.sql](database/supabase/mobile_backend.sql) after the Laravel migrations and configure the public project URL/key as build-time defines.

```powershell
cd mobile
flutter pub get
flutter run `
  --dart-define=SUPABASE_URL=https://fywtxrptaqcmkzsgfehp.supabase.co `
  --dart-define=SUPABASE_ANON_KEY=YOUR_SUPABASE_PUBLISHABLE_KEY
```

See [mobile/README.md](mobile/README.md) for employee account linking and APK build instructions. The Flutter client must never receive the Laravel server's Supabase secret key or a PostgreSQL connection string.

## Verification

```powershell
php artisan test
vendor/bin/pint --test
npm.cmd run build
cd mobile
flutter analyze
flutter test
```

Test configuration uses in-memory SQLite. It does not connect to the database configured in `.env`.

## Existing data migration

SQLite-to-Supabase migration is an explicit operator-run operation. Review [docs/data-migration.md](docs/data-migration.md), make protected backups of both databases, run the dry-run and preflight, then require the explicit execution flag. This repository does not connect to or migrate a live database automatically.

## Project documentation

- [Initial audit and implementation plan](docs/project-audit-and-implementation-plan.md)
- [Official ITSD 85 final-project compliance matrix](docs/ITSD85-final-project-compliance.md)
- [CI/CD lab adaptation and verification runbook](docs/cicd-runbook.md)
- [Architecture and runtime services](docs/architecture.md)
- [Backup, restore, and deployment](docs/deployment.md)
- [Requirements traceability](docs/requirements-traceability.md)
- [Complete system documentation](docs/Complete%20System%20Documentation.md) ([PDF](docs/Complete%20System%20Documentation.pdf))
- [Complete database schema](docs/Complete%20Database%20Schema.md) ([PDF](docs/Complete%20Database%20Schema.pdf))
- [System features and role permissions](docs/System%20Features%20and%20Role%20Permissions.md) ([PDF](docs/System%20Features%20and%20Role%20Permissions.pdf))
- [Chatbot evaluation set](docs/evaluation/chatbot-questions.json)
- [OpenAPI document](docs/openapi.yaml)
- [Web system API reference](RGV-System-API.md)
- [Mobile API reference](RGV-Mobile-API.md)

MIT License.
