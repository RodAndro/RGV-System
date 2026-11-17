# ITSD 85 Final Project Requirements: Repository Compliance Matrix

**Rubric source:** `ITSD85_Final_Project_Requirements_and_Guidelines (2).pdf`, Version 1.1, issued 20 October 2026.  
**Reviewed:** 11 November 2026, Asia/Manila.  
**Project revision:** `cb075d13bdcd0f3789f37e60d8dd88737dc8f851` plus the existing uncommitted project worktree and the CI/CD follow-up edits described in [the runbook](cicd-runbook.md).  
**Scope:** Code/configuration and local evidence only. GitHub settings/history, Google Classroom, the public deployment, actual users, and instructor approvals were not available.

Status meanings: **PASS** repository implementation/evidence meets the criterion; **PARTIAL** implementation exists but a required part or proof is missing; **FAIL** a mandatory requirement is absent or contradicted by the current architecture; **UNVERIFIED** depends on external evidence/access.

## Executive result

**NOT READY FOR FINAL SUBMISSION OR DEFENSE.** Main blockers are the server-rendered web portal sharing the Laravel app rather than a separate client served through a proxy, the lack of full resource CRUD through the REST API, incomplete OpenAPI UI/coverage, absent formal documentation PDFs and research paper, and missing public deployment/UAT/evaluation/release evidence.

The project does have useful working foundations: tested booking/borrowing business logic, role controls, a Flutter API client, PostgreSQL migrations, local Ollama integration with a fallback, Docker/Caddy configuration, health endpoint tests, GitHub workflow configuration, and useful Markdown/PDF documentation. In this follow-up, CI/CD also gained Compose/container checks, SHA-tagged app images, and a runbook. These repository artifacts do not prove a successful GitHub run, a configured runner, a public HTTPS deployment, or a restore drill.

## Project proposal and milestones

| Requirement | Status | Evidence / gap |
|---|---|---|
| Real problem and identifiable users | PASS | The README/overview and app workflows identify public customers, employees, and administrators and support service bookings, inventory, and borrowing. |
| Enabling technology is essential to the system's purpose | FAIL | Ollama chatbot/report insights are supporting features; booking, inventory, and borrow/return workflows operate without AI. The rubric rejects decorative AI and CRUD-only projects. Resolve this against the approved proposal/instructor before claiming T1. |
| Originality and not reused from another class/term | UNVERIFIED | Repository history alone cannot establish this. |
| Feasibility and free-to-operate stack | PARTIAL | Laravel/PostgreSQL/Ollama/Docker/Caddy are open-source options, but no approved scope/timeline or stable free host is evidenced. |
| Proposal PDF, three optional titles, team (2–3), responsibilities, objectives, 5–8 user stories, integration plan, stack, ERD/API resources, privacy, eight-week timeline | UNVERIFIED | No approved proposal PDF or team details were found in the repository. |
| Instructor proposal approval | UNVERIFIED | Approval is external. |
| M1–M5 milestone evidence (proposal, design, MVP, deployed feature complete, evaluation/docs) | UNVERIFIED | No Classroom milestone submissions or instructor progress-check evidence was available. |

## Minimum technical requirements

### Client application (F1–F6)

| ID | Requirement | Status | Evidence / missing work |
|---|---|---|---|
| F1 | Separate frontend client obtains all data through team REST API; no direct DB | FAIL | Flutter uses Laravel REST, but primary web UI is Laravel Blade in the same app (`resources/views/`, `routes/web.php`). Current Caddy sends all paths to Laravel, not a distinct client container. |
| F2 | At least 8 functional screens/pages with login, dashboard, searchable/filterable/paginated lists, create/edit, detail | PASS | Numerous web routes/templates plus 10 Flutter screen files. Route/template inventory is in the [audit report](../ITSD85-Compliance-Audit-2026-11-11.md). Live walkthrough is still needed. |
| F3 | Client validation/errors; API errors visible | PASS | Blade and Flutter validation/error-state components exist; local tests cover selected invalid inputs. Expand workflow coverage. |
| F4 | Responsive at 360 px and desktop | PARTIAL | Responsive layouts exist; no captured 360 px and desktop verification or screenshot evidence. |
| F5 | Role-based menus/actions | PASS | Separate admin/employee navigation and route guards exist; backend role enforcement is separately covered under B4. |
| F6 | Labels, keyboard access, contrast | PARTIAL | Labeled controls exist, but keyboard/focus/contrast review was not performed. |

### Backend REST API (B1–B9)

| ID | Requirement | Status | Evidence / missing work |
|---|---|---|---|
| B1 | REST conventions under `/api/v1`, plural lowercase hyphenated resources, methods/status/JSON | PARTIAL | Mobile API is under `/api/v1/mobile`; public/health/docs paths are outside versioned resources; workflow actions use action routes; admin CRUD is mostly web/session routes. |
| B2 | Full API CRUD for at least 2 main resources and at least 4 related entities | FAIL | Inventory API is read-only; mobile borrow API does not provide standard full CRUD on two resources. Full web CRUD is not a substitute for API CRUD in this criterion. |
| B3 | Hashed passwords and JWT or secure session/token mechanism | PASS | Laravel password hashing, session auth, Sanctum tokens, MFA; tests cover login/MFA/revocation. |
| B4 | At least 2 roles checked on every protected backend endpoint | PASS | Admin/employee middleware, active employee and Sanctum ability checks, ownership checks; selected unauthorized cases pass tests. Extend endpoint-by-endpoint tests as API expands. |
| B5 | Server validation on every write; consistent JSON error object | PARTIAL | Validation exists, but Laravel's `{message, errors}` responses differ from the rubric's consistent `{error:{code,message,details}}` envelope. |
| B6 | Page/limit/search/filter/sort and accurate metadata for list endpoints | PARTIAL | Some lists provide pagination/search/filter; public inventory uses cursor pagination and the API lacks a consistent page/limit/sort/meta contract. |
| B7 | OpenAPI 3, Swagger UI/Redoc at `/api/docs`, every endpoint and response documented; export `docs/api/openapi.yaml` | PARTIAL | OpenAPI 3 exists at `docs/openapi.yaml` and `/api/docs` serves raw YAML. Missing required export path, interactive UI, complete schemas/examples/statuses/role requirements, and endpoint parity. |
| B8 | `/api/health` returns status, version/commit and database connectivity | PASS | Implemented and covered by `tests/Feature/ApiHealthTest.php`; live deployment commit remains unverified. |
| B9 | Request/error logging without password/token leakage | PASS | Logging middleware and exception reporting exist; inspected logging avoids request body/query credentials. Production log retention not verified. |

### Database (D1–D4)

| ID | Requirement | Status | Evidence / missing work |
|---|---|---|---|
| D1 | PostgreSQL/MySQL relational schema with PK/FK/constraints | PASS | PostgreSQL config and related migrations with keys, foreign keys, indexes, uniqueness, and Eloquent relationships. Refresh the older schema PDF. |
| D2 | Schema migrations in repo and sample-data seed script | PASS | `DatabaseSeeder` now seeds roles, categories, and sample inventory only in local/testing; optional demo users require local `.env` credentials. `tests/Feature/DatabaseSeederTest.php` verifies seeded entities. No production seeding was run. |
| D3 | DB credentials from environment, not hard-coded | PASS | `.env.example`, config, and Compose use environment values; real `.env` remains ignored. |
| D4 | Documented backup/restore and one successful restore test | PARTIAL | Backup/restore procedures and backup code exist; no separate-database restore drill record with date/file/rows checked. |

### Quality and security (Q1–Q5)

| ID | Requirement | Status | Evidence / missing work |
|---|---|---|---|
| Q1 | Every API endpoint has a success and failure test; backend line coverage ≥60% | PARTIAL | `php artisan test`: 119 passing, 431 assertions, but endpoint coverage is incomplete and local coverage cannot run without PCOV/Xdebug. CI requests PCOV/60%, but no GitHub run evidence. |
| Q2 | Frontend login test and one main workflow test | PARTIAL | Flutter login widget tests and model/repository tests exist; no complete end-to-end main workflow test. |
| Q3 | Frontend and backend lint in CI | PARTIAL | Backend scoped Pint and mobile Dart format/analyze are in workflow; root web JS/Blade lint is not configured, and scoped Pint is not full-project lint. Full local Pint fails baseline files. |
| Q4 | OWASP basics, restricted CORS, rate-limited login | PARTIAL | ORM, escaped Blade, access checks, throttling and security headers exist. CORS restriction and a complete OWASP review are not evidenced. |
| Q5 | No repository secrets; `.env.example` contains all required variables with dummy values | PASS | `.env` is ignored and example values are placeholders/defaults. Do not commit host secrets or runner credentials. |

### Deployment (DP1–DP6)

| ID | Requirement | Status | Evidence / missing work |
|---|---|---|---|
| DP1 | Frontend, API, DB in Docker; one Compose up starts system | PARTIAL | Compose defines app, Ollama, and Caddy, while Laravel connects to hosted Supabase PostgreSQL. Client is part of the Laravel app, not a separate frontend container. Docker CLI/build unavailable locally. |
| DP2 | Reverse proxy serves frontend and routes `/api` to backend | FAIL | Caddy proxies all requests to `app:8080`; it does not route a separate frontend and API service. |
| DP3 | Public HTTPS works during evaluation | UNVERIFIED | No public URL/DNS/host access was provided. |
| DP4 | Every PR tests/lints; every merge to main deploys and passes health check | PARTIAL | Workflow now scopes PRs to main, includes manual dispatch, test gates, SHA-tagged deployment, migration, and commit-matching health check. No successful remote run or deployment evidence. |
| DP5 | Active main branch ruleset requires PR and passing checks | UNVERIFIED | Must be configured and evidenced in GitHub settings. Required checks should be backend/frontend/mobile. |
| DP6 | Database persists across redeploys | PASS | Supabase manages database persistence; actual project backup/restore and retention were not tested. |

### Technology integration (T1–T6)

| ID | Requirement | Status | Evidence / missing work |
|---|---|---|---|
| T1 | Enabling technology is essential to the main workflow | FAIL | Current operational workflow can run without chatbot/AI. This is a project-scope/proposal mismatch, not a documentation-only gap. |
| T2 | Technology accessible only through backend REST API; no model files/keys in client | PASS | Flutter/browser call Laravel; Ollama connection and database retrieval are server-side. |
| T3 | Free/open-source and graceful unavailable behavior | PASS | Local Ollama is configured; tests cover model unavailable and deterministic fallback. Actual server/model availability unverified. |
| T4 | Each use records input reference, output, confidence/score, model/version, timestamp, user | PARTIAL | `chatbot_interactions` records question hash/reference, response, model, outcome, elapsed time, user, timestamp. A confidence/score is not recorded. |
| T5 | Metric + test set and reported results | PARTIAL | 20 chatbot prompts exist; no actual correctness/grounding, unsupported-answer, latency, or other metric results in docs. |
| T6 | Machine output disclosed, human confirmation, model/dataset/library license credit | PASS | Assistant output is labeled/advisory; it does not perform business actions; model/license is described. Confirm all final UI/documents include the notice. |

## Required documentation set (D1–D5)

| ID | Deliverable | Status | Rubric comparison |
|---|---|---|---|
| D1 | System Documentation, 40–80 pages + appendices; editable source and PDF | FAIL | Current `RGV-System.pdf` is 8 pages. Existing Markdown, feature specification (17-page PDF), schema (20-page PDF), and source appendix (7-page PDF) are useful inputs but are not the required complete 40–80 page D1 with chapters 1–8, approval sheet, traceability, evaluation results, diagrams, appendices, 15 references, and editable source. |
| D2 | User Manual PDF, 10–25 pages, step-by-step per role with screenshots | FAIL | `RGV-User-Manual.md` exists, but no user-manual PDF or deployed-version screenshots were found. |
| D3 | API reference: every endpoint; `docs/api/openapi.yaml`; live `/api/docs`; PDF appendix in D1 | PARTIAL | Spec and Markdown API references exist, but path/UI/spec completeness and D1 appendix are missing. |
| D4 | IEEE research paper PDF, 6–8 pages, measured evaluation, ≥12 references | FAIL | No paper PDF/source or actual measurements found. |
| D5 | README, 1–3 screens, required project/team/live/API/features/architecture/stack/run/test/deploy/demo/docs/license headings | PARTIAL | README has overview, stack, local and Docker setup, API and verification sections. It lacks verified live URL, team/section/instructor, architecture image, demo accounts, and complete D1–D4 links. These fields cannot be invented. |

D1/D2 formatting also requires page numbering, figures/tables list, references, editable source, embedded PDF fonts/bookmarks, and screenshots with sample data only. These details were not fully verifiable from the present files.

## Submission, defense, and evaluation evidence

| Requirement | Status | Evidence / missing work |
|---|---|---|
| Correct team GitHub repository with source/docs/tests/Compose/workflow | PARTIAL | Git remote is the RGV repo; intended final team name, teammates, instructor collaborator, and release contents are not established. Existing worktree includes uncommitted changes. |
| Weekly member commits, issues/project board, reviewed PRs | UNVERIFIED | GitHub member attribution, issues, board, and review history unavailable. |
| v1.0 tag and release with matching paper/docs and current live commit | FAIL | No local tag found. No matching final documents or verified live SHA. |
| Google Classroom package and Turned-in status | UNVERIFIED | Classroom unavailable. Required items include repo/release/live links, D1/D2/D4 PDFs, slides, demo video, individual contribution reports, and post-defense scoresheet. |
| UAT with ≥5 target users and consent/anonymized results | UNVERIFIED | No UAT instruments, consent records, or results supplied. Do not fabricate responses. |
| SUS (10 items) and ISO/IEC 25010 quality questionnaire results | FAIL | No actual respondent/sample statistics, means, or interpretations found. |
| Defense/demo: 15-minute presentation + 15-minute Q&A; public system, roles, workflow, authorization, docs, AI/fallback/logging/evaluation, CI deploy and commit | UNVERIFIED | No public endpoint, release, evaluation results, slide deck, or demonstrated CI history. A timed checklist is in the [CI/CD runbook](cicd-runbook.md); demonstration must still occur on the deployed system. |
| AI tools declared in D1/D4 and team can explain submitted work | UNVERIFIED | No formal acknowledgements or member declarations. This audit and implementation assistance should be disclosed if the team uses its output. |

## Local verification snapshot

- Laravel: 119 tests passed, 431 assertions. Local coverage command cannot run because PHP lacks PCOV/Xdebug.
- Flutter: after formatter fixes, Dart format check passed, analysis reported no issues, and 15 tests passed.
- Web: `npm ci` and Vite build passed. `npm ci` reported 13 dependency advisories (1 low, 2 moderate, 8 high, 2 critical); dependency updates were not applied in this CI/CD task.
- PHP style: workflow's scoped Pint check passed; full-project Pint still reports existing findings.
- Workflow and Compose YAML: syntax parsed successfully with Prettier. Docker Compose config/image build could not run because Docker is not installed locally.
- GitHub CI, runner, branch rules, public HTTPS, live DB, restore, Ollama evaluation, Classroom, UAT: not verified or performed.

## Priority actions

1. Resolve with the approved proposal/instructor how the project will meet the requirement for enabling technology essential to the main workflow. Re-scope the workflow only with approval; do not claim the optional chatbot already satisfies T1.
2. Decide how to meet F1/DP1/DP2: separate the primary client from Laravel, serve it through Caddy, route `/api` to the backend, and expose versioned CRUD endpoints for at least two main resources. This is a significant architecture change and should be planned against existing behavior.
3. Complete B1/B5/B6/B7: REST resource behavior, uniform validation errors, standard page/limit/filter/sort metadata, interactive OpenAPI documentation and `docs/api/openapi.yaml` parity.
4. Add API endpoint success/failure tests, login plus main-workflow client E2E test, full front/back lint, and obtain actual ≥60% coverage evidence in CI. Triage the npm advisories.
5. Run and record a separate-database backup/restore test; add a restore date, artifact reference, and checks performed to the deployment manual.
6. Perform integration evaluation on the real local model, including correctness/grounding and response time across the 20-question set. Add a traceability score/confidence only if the system can calculate a defensible value.
7. Perform consent-based UAT with at least five target users; report anonymized SUS and ISO/IEC 25010 sample sizes, means, and interpretations.
8. Produce D1–D5 to the prescribed formats/lengths, plus diagrams and screenshots from the final deployed revision. Add real team and instructor details only from the team.
9. Configure the production runner, `APP_DOMAIN`, protected main rules, and stable HTTPS; demonstrate passing deploy, a failing PR that does not deploy, and rollback. Capture actual evidence.
10. Review and merge intended work, create `v1.0`, publish matching release documents, complete Classroom submission, and prepare the signed scoresheet and defense materials.

This matrix reports source and available local evidence. It does not represent instructor approval, official scoring, academic originality clearance, successful deployment, or user study findings.
