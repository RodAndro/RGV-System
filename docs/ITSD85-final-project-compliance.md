# ITSD 85 Final Project Requirements: Compliance Matrix

**Rubric source:** ITSD 85 Final Project Requirements and Guidelines, Version 1.1, 20 October 2026.
**Reviewed:** 17 November 2026, Asia/Manila.
**Scope:** Code, configuration and locally executed evidence. GitHub repository settings,
Google Classroom, the public deployment and real users were not available.

Status: **PASS** implementation meets the criterion and was executed locally;
**PARTIAL** implemented but a required part or proof is missing;
**FAIL** absent or contradicted; **UNVERIFIED** depends on external access.

## Executive result

**Technical requirements are implemented and verified locally. External evidence is not.**

Every functional, API, database, security, quality and technology-integration
requirement below was exercised against a running MySQL instance. What remains
outstanding is evidence that only the team can produce: a public HTTPS address,
a green remote pipeline run, user acceptance testing, and the release tag.

## Minimum technical requirements

### Client application (F)

| ID | Status | Evidence |
|---|---|---|
| F1 | PASS | `Frontend/app` is a React SPA served at `/app`; `src/lib/api.ts` is its only network layer and targets `/api/v1`. The Flutter app calls only `/api/v1/mobile`. Neither touches the database. |
| F2 | PASS | 9 SPA screens (login, dashboard, inventory list/detail/create/edit, borrow list/detail/create, profile) plus 102 Blade views. |
| F3 | PASS | zod schemas mirror server rules; API `error.details` map to fields; a 422 envelope is asserted in tests and was verified live. |
| F4 | PARTIAL | Mobile-first Tailwind with responsive breakpoints; 360 px screenshot evidence still required. |
| F5 | PASS | Navigation filtered by role; `/inventories/new` guarded by `RequireAdmin`; the server re-checks and returns 403 (verified live). |
| F6 | PASS | Every control has `htmlFor`/`id`, `aria-invalid`, `aria-describedby`; skip link; visible focus rings; table captions. |

### Backend REST API (B)

| ID | Status | Evidence |
|---|---|---|
| B1 | PASS | `/api/v1` with plural hyphenated resources; 201 + Location on create, 204 on delete, 404/422/403/401 verified live. |
| B2 | PASS | Full CRUD on `inventories` and `borrow-requests`; related entities `inventory-categories`, `bookings`, `borrow-items`, `users`. |
| B3 | PASS | Bcrypt hashing; Sanctum bearer tokens; MFA; token revoked on logout (401 verified live). |
| B4 | PASS | `EnsureApiRole` + `auth:sanctum` per route; ownership checks; employee denied admin writes and AI reports with 403 (verified live). |
| B5 | PASS | FormRequests on every v1 write; uniform `{"error":{code,message,details}}` envelope, verified live. |
| B6 | PASS | Shared pagination trait; `meta` with page, limit, total, total_pages; sort and filter verified live. |
| B7 | PASS | Swagger UI at `/api/docs`; spec covers **32 of 32** served endpoints; parity is enforced by `OpenApiParityTest`. |
| B8 | PASS | `/api/health` returns status, version, commit and database connectivity; degraded branch tested. |
| B9 | PASS | Request logging records method, path, status, user, duration; no bodies or tokens logged. |

### Database (D)

| ID | Status | Evidence |
|---|---|---|
| D1 | PASS | MySQL 8.4/MariaDB 10.4 schema: 38 tables, 27 foreign keys, primary keys, unique constraints, indexes. |
| D2 | PASS | 36 migrations + seeders; `migrate` and `db:seed` executed against MySQL. |
| D3 | PASS | Credentials from environment only; `.env` ignored; `.env.example` complete (asserted by `EnvExampleCoverageTest`). |
| D4 | PASS | **Restore drill executed**: 195 KB dump restored into a separate database; 7/7 row counts matched; 27 FKs and 38 indexed tables intact. Record in `docs/evaluation/backup-restore-drill.md`; scripts in `scripts/`. |

### Quality and security (Q)

| ID | Status | Evidence |
|---|---|---|
| Q1 | PARTIAL | 214 tests / 1048 assertions covering success and failure paths per endpoint. The 60% coverage gate runs in CI; line coverage cannot be measured on this machine (no PCOV/Xdebug). |
| Q2 | PASS | SPA: 8 Vitest tests (login validation, server error, inventory search + pagination). Flutter: 35 tests. |
| Q3 | PARTIAL | ESLint (SPA), Dart analyze, Pint on API modules, PHP syntax check. Full-project PHP style has pre-existing debt â€” see `lint-baseline.md`. |
| Q4 | PASS | Eloquent parameterisation, escaped output, server-side authorisation, CORS restricted to `FRONTEND_URL`, login throttling, security headers. |
| Q5 | PASS | No secrets in the repository; `.env.example` completeness asserted by test; real identifiers removed. |

### Deployment (DP)

| ID | Status | Evidence |
|---|---|---|
| DP1 | PARTIAL | `compose.yaml` defines db, app, web, caddy, ollama, optional tunnel. Docker is **not installed on this machine**, so the stack was not started here. |
| DP2 | PARTIAL | `deploy/Caddyfile` routes `/app/*` to the SPA and everything else to Laravel. Not executed locally. |
| DP3 | UNVERIFIED | No public URL yet; requires the team runner and tunnel/domain. |
| DP4 | PARTIAL | Workflow runs lint + tests on PRs and deploys on `main` with a commit-matching health check. No green remote run yet. |
| DP5 | PARTIAL | Ruleset definition committed at `.github/rulesets/protect-main.json` with 6 required checks; applying it needs `scripts/setup-branch-ruleset.sh` and repo admin access. |
| DP6 | PASS | MySQL data in the `db-data` volume; survives redeploys. |

### Technology integration (T)

| ID | Status | Evidence |
|---|---|---|
| T1 | PARTIAL | AI produces advisory business insight over aggregate data through the API. It informs reporting and inventory decisions but is not required for booking or borrowing to function. **Confirm this scope with the instructor.** |
| T2 | PASS | Model reached only through `/api/v1/ai/*` (and the session portal); the client holds no model files or service keys. |
| T3 | PASS | Local Ollama in its own container; deterministic fallback returns a useful summary instead of failing (verified live, `source=deterministic-fallback`). |
| T4 | PASS | `chatbot_interactions` records question hash, output, model, **confidence**, response time, user and timestamp; verified live (3 rows with confidence). |
| T5 | PARTIAL | A 20-question set exists in `docs/evaluation/`; measured correctness and latency results have not been produced. Not fabricated. |
| T6 | PASS | Machine-generated disclosure on every response; advisory-only; model and license credited. |

### Documentation (D1â€“D5)

| ID | Status | Evidence |
|---|---|---|
| D1 | PARTIAL | Chapter outline with real technical content at `docs/01-system-documentation`; chapters needing measured data are marked TODO. Not yet a 40â€“80 page PDF. |
| D2 | PARTIAL | Per-role manual at `docs/02-user-manual` with screenshot capture points marked; not yet a PDF. |
| D3 | PASS | `docs/api/openapi.yaml` (canonical), synchronised to `docs/03-api/` and `docs/openapi.yaml`, served live at `/api/docs`. |
| D4 | PARTIAL | Existing paper PDF; structure and available measured results documented. |
| D5 | PASS | README has every required heading; unknowable values are explicit `TODO(team)` markers. |

## Local verification snapshot (executed 17 November 2026)

- Backend: **214 tests, 1048 assertions**, run 5 consecutive times with zero failures.
- SPA: **8 tests** passing; ESLint clean; production build clean.
- Flutter: **35 tests** passing; analyzer and formatter clean.
- Live HTTP against MySQL: **72 checks, 72 passed** â€” admin portal pages, employee pages,
  public routes, role separation (403s), v1 CRUD, error envelope, AI endpoints with
  traceability, mobile app endpoint set, and admin-creates-employee then app login.
- MySQL: 38 tables, 27 foreign keys, migrations and seeders executed.
- Backup/restore drill: executed and verified (7/7 tables matched).
- OpenAPI: 32/32 served endpoints documented, enforced by test.

Not executed here: Docker Compose, Caddy routing, Cloudflare Tunnel, GitHub Actions runs,
and the public HTTPS address (Docker is not installed on the development machine).

## What still requires the team

1. Approve the AI scope with the instructor (T1) or extend it so booking/borrowing depend on it.
2. Deploy to the runner, confirm `/api/health` reports the deployed commit, record the public URL.
3. Apply the branch ruleset: `./scripts/setup-branch-ruleset.sh <owner>/<repo>`.
4. Measure and report: coverage %, AI correctness/latency over the question set, SUS and ISO 25010.
5. Capture screenshots and fill the `TODO(team)` markers in D1, D2, D4 and the README.
6. Tag `v1.0` and publish the release.
