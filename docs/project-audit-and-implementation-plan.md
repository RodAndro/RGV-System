# Project Audit and Implementation Plan

> Historical inventory and plan: later repository changes supersede some statements below. See the superseding architecture status at the end for the current database and mobile-client design.

**Audit date:** 2026-11-11  
**Scope:** Repository source, configuration templates, manifests, tests, deployment documentation, and tracked project structure. The live `.env` was deliberately not read. No live database or external account was queried during this audit.

## Existing architecture

- Laravel 12.58.0 / PHP 8.2 backend with Eloquent models, migrations, policies via middleware and Spatie roles, Blade/Tailwind/Alpine web UI, and Vite assets.
- Supabase PostgreSQL is the canonical database. `.env.example` targets the supplied direct endpoint with TLS required; IPv4-only deployments use shared Session pooler values from the Dashboard. `config/database.php` also retains SQLite and MySQL/MariaDB for development and tests.
- Flutter Android employee client under `mobile/`; authentication and business data flows use Laravel Sanctum REST endpoints.
- Existing web features include public booking and tracking, admin and employee portals, inventory, borrowing, reports, exports, imports, notifications, audit logs, backups, MFA, and QR workflows.
- Existing API includes public inventory lookup and authenticated Sanctum mobile endpoints for employee auth, dashboard, inventory, borrow/return, and profile workflows. Most admin web CRUD remains server-rendered and is not exposed as a versioned REST API.
- Local filesystem storage is already the application default for private files, reports, imports, exports, and backups. No S3 disk is selected by the example environment.
- PHPUnit feature coverage exists for authentication, mobile API, chatbot, backup, and business workflows. Test configuration uses in-memory SQLite.

## Dependency inventory

| Component | Current implementation and location | Purpose | Decision |
|---|---|---|---|
| Supabase PostgreSQL | `.env.example`, `config/database.php`, deployment docs | Canonical relational database for website and mobile business data | Retain as the single source of truth. Credentials come only from deployment environment or the Supabase Dashboard. |
| Legacy Supabase Auth / Postgres RPC | `database/supabase/mobile_backend.sql`, historical SQL migrations, `supabase_auth_id` column | No current Flutter runtime call sites; retained historical compatibility artifacts | Do not invoke from Flutter or apply as a separate client architecture. Preserve existing Supabase objects and migration history. |
| Gemini API | `GeminiService`, `ChatbotController`, `.env.example`, `config/services.php` | Hosted fallback when local Ollama is unavailable | Remove from chatbot flow; use local-only Ollama and an explicit unavailable response. Do not send database context to hosted AI. |
| Ollama | `OllamaService`, AI report and chatbot controllers | Local language model inference | Retain; provide an optional internal Compose service and graceful offline behavior. |
| OpenAI PHP client | `composer.json` / `composer.lock` | No application call sites found by source search | Remove if lockfile can be updated without introducing unrelated dependency churn. |
| Local filesystem | `config/filesystems.php`, import/export/backup jobs and controllers | Private uploads, backups, reports, and temporary work | Retain. Persist `storage` in deployment and keep private disk outside `public`. |
| SMTP / email | Laravel mail config and notification classes | Password resets, MFA, email notifications | Retain as configurable optional delivery infrastructure; local log mailer remains suitable for development. |
| Firebase Hosting / Google Cloud Run | `docs/firebase-cloud-run-deployment.md` | Historical hosting reference | Current documented deployment uses Laravel Compose, Supabase PostgreSQL, Ollama, and Caddy. |
| GitHub Actions / self-hosted runner | `.github/workflows/ci-cd.yml` and `docs/cicd-runbook.md` | CI and main-branch deployment | Retain the documented check pipeline; production runner and environment approvals remain operator-configured. |

No credential values were included in this report.

## Findings and risks

1. The mobile client has been migrated to Sanctum REST endpoints for login, MFA, session persistence, inventory, requests, returns, dashboard, and profile flows. Legacy SQL remains unreferenced by Flutter.
2. The chatbot uses Ollama first, then sends report context to Gemini, then uses a local rule-based response. The hosted fallback conflicts with the local-only privacy requirement.
3. Existing database migrations include Supabase-related changes. They are historical schema changes and must not be deleted or replayed against a live database as part of this work.
4. Compose, reverse proxy configuration, CI workflow, `/api/health`, `/api/docs`, and an OpenAPI spec are present; Docker validation requires an available Docker CLI.
5. The web application has many working modules and a broad test suite. A wholesale folder move would create avoidable compatibility risk; retain Laravel's existing structure.
6. Two untracked files were present before implementation (`RGV-System-Source-Code-Appendix.pdf` and `generate_source_appendix.py`); they are user work and must remain untouched.
7. The local `.env` may target a live/shared database. It was not read. Database-changing operations must use the test suite's isolated SQLite configuration or a newly configured local environment.

## Safe implementation plan

1. Keep current project layout and all existing business workflows.
2. Use Supabase PostgreSQL as the canonical relational database; Compose runs Laravel, an internal Ollama service, and Caddy as the only public entry point.
3. Add health and OpenAPI endpoints while preserving current endpoint paths for existing clients.
4. Make the chatbot local-only, split retrieval from generation, log privacy-conscious interaction metadata, and provide a documented 20-question evaluation set. Keep an unavailable-model fallback that states the limitation and supported database-derived facts.
5. Route Flutter workflows through the existing Laravel API, extending endpoints only where the current app needs missing behavior.
6. Remove unused hosted AI integration and update environment examples/docs. Preserve legacy Supabase schema history and allow existing accounts to continue using their Laravel credentials.
7. Add CI and safe deployment documentation that never executes pull request code on the privileged deployment runner.
8. Run focused Laravel and Flutter checks where the local toolchain permits. Never run a destructive migration, database reset, or live backup/restore during this task.

## Verification constraints

The current project includes installed PHP/Composer and Node dependencies, but `npm` PowerShell script execution is restricted and Flutter availability must be checked independently. Docker, a local Ollama model, GitHub secrets/runner, public HTTPS tunnel, and live server credentials are environment-dependent and may not be available here. Results will be reported only for checks actually run.

## Superseding architecture status (2026-11-11)

This section reflects the current code after the Laravel/Sanctum mobile migration and Supabase source-of-truth work. Earlier inventory and plan statements above describe the repository before those changes and are no longer the runtime architecture:

- Laravel is the only business-data client and uses Supabase-hosted PostgreSQL. The Flutter app uses Laravel's Sanctum API for auth, validation, authorization, inventory, borrowing, returns, and profile workflows. The mobile package has no Supabase SDK or privileged database credentials.
- `.env.example` points to the project direct PostgreSQL host with TLS required. IPv4-only hosts must copy the shared Session pooler host and username from the Supabase Dashboard. The exact pooler host and all credentials remain deployment environment values.
- Compose runs Laravel, Ollama, and Caddy; it no longer starts a second application PostgreSQL database.
- `rvg:migrate-sqlite-to-supabase` is dry-run by default, checks source orphans and target schema, requires explicit execution and backup flags, writes row-to-row identity mappings, preserves target rows, and reconciles mapped records. It is not run automatically and has not been run against a live database.
- Legacy SQL RPC files and historical Supabase-related migrations remain for record-keeping; they are not used by the current Flutter runtime.
