# ITSD 85 requirements traceability

> **Superseded:** For the official Version 1.1 rubric and current status, use [docs/ITSD85-final-project-compliance.md](ITSD85-final-project-compliance.md). This earlier snapshot predates the official PDF review and should not be used as the final compliance record.

Statuses reflect repository evidence as audited on 2026-11-11. “Partial” means code or configuration exists but the complete requirement or runtime verification is missing. GitHub branch protection, server setup, public HTTPS, and local model evaluation require access outside this repository.

## Client application

| ID | Status | Evidence / remaining work |
|---|---|---|
| F1 | Implemented | Flutter repositories call Laravel `/api/v1/mobile`; no direct database client remains in `mobile/lib`. |
| F2 | Partial | Existing mobile screens cover login, MFA, dashboard, inventory, request, return, and account flows; count and screen completeness still need review against the final rubric. |
| F3 | Partial | Forms validate locally and Laravel validates server-side; workflow-level error coverage should expand. |
| F4 | Partial | Responsive Flutter layouts exist; 360 px/desktop evidence has not been captured. |
| F5 | Implemented | Employee API routes require active employee and employee token ability; web routes enforce admin/employee middleware. |
| F6 | Partial | Flutter Material controls provide labels/navigation; accessibility and contrast audit is still needed. |

## Backend REST API

| ID | Status | Evidence / remaining work |
|---|---|---|
| B1 | Partial | JSON mobile and inventory endpoints use HTTP methods; legacy mobile prefix retained under `/api/v1/mobile`. Admin CRUD remains primarily web routes. |
| B2 | Not implemented | API does not yet provide full CRUD for two core resources and four related entities. Existing web workflows do provide this functionality. |
| B3 | Implemented | Laravel password hashes, login throttling, Sanctum tokens, MFA challenge ability, and token revocation. |
| B4 | Implemented | Server-side admin/employee middleware, ownership checks, active account checks, and token ability checks. |
| B5 | Partial | Laravel validation and JSON errors exist; error structure is not globally normalized for every endpoint. |
| B6 | Partial | Public inventory API supports cursor pagination and filters; broader API sorting/filtering needs standardization. |
| B7 | Implemented | OpenAPI YAML is served at `/api/docs` from `docs/openapi.yaml`; keep it aligned as routes change. |
| B8 | Implemented | `GET /api/health` returns status, version, commit, and database connectivity. |
| B9 | Implemented | API request logs omit body, query strings, and credentials; exception reports log only exception class and request ID. |

## Database

| ID | Status | Evidence / remaining work |
|---|---|---|
| D1 | Implemented | Relational Laravel migrations define primary keys, relations, and constraints. |
| D2 | Implemented | Existing migrations/seeders retained; new chatbot log schema is additive. |
| D3 | Implemented | Database connection is environment-based; `.env.example` targets the supplied Supabase PostgreSQL project with TLS required. |
| D4 | Partial | Backup and restore steps are documented in `docs/deployment.md`; no live database restore was attempted. |

## Quality and security

| ID | Status | Evidence / remaining work |
|---|---|---|
| Q1 | Partial | PHPUnit tests cover several workflows; endpoint-wide success/failure coverage and 60% coverage result must be confirmed by CI. CI enforces a 60% backend coverage floor. |
| Q2 | Partial | Flutter widget/model tests include login and business data parsing; a complete end-to-end primary workflow test remains. |
| Q3 | Partial | CI runs Pint, Dart format/analyze, and production asset build; a dedicated JS/Blade lint step is not configured. |
| Q4 | Partial | Eloquent/query binding, Blade escaping, RBAC, rate limiting, and security headers are present; a formal security review remains. |
| Q5 | Implemented | `.env.example` uses empty/dummy-only values and contains no real credentials. |

## Deployment

| ID | Status | Evidence / remaining work |
|---|---|---|
| DP1 | Implemented | `compose.yaml` defines Laravel, Ollama, and Caddy; Laravel connects to canonical Supabase PostgreSQL over TLS. |
| DP2 | Implemented | `deploy/Caddyfile` proxies to Laravel; Caddy alone publishes ports 80/443. |
| DP3 | Blocked by environment | Public HTTPS needs DNS/host setup or operator-managed Cloudflare Tunnel. |
| DP4 | Partial | `.github/workflows/ci-cd.yml` tests PRs and deploys successful `main` pushes on a labeled self-hosted runner; workflow has not run in GitHub. |
| DP5 | Blocked by external settings | Configure protected `main`, required checks, and production environment in GitHub repository settings. |
| DP6 | Implemented | Supabase owns database persistence; application storage uses named volumes and redeploy does not delete volumes. |

## Technology integration

| ID | Status | Evidence / remaining work |
|---|---|---|
| T1 | Implemented | Local Ollama language model is the AI enabling technology. |
| T2 | Implemented | Flutter reaches AI only through Laravel; browser chatbot posts to the Laravel backend. |
| T3 | Implemented | Ollama is open source, Qwen3 1.7B is Apache 2.0, and the app handles an unavailable model/service gracefully. |
| T4 | Implemented | `chatbot_interactions` stores user reference, hashed question reference, aggregate source, generated response, model, outcome, and elapsed time. |
| T5 | Partial | Twenty representative prompts are in `docs/evaluation/chatbot-questions.json`; correctness, grounding, unsupported-answer rate, relevance, and latency need a real Ollama evaluation run. |
| T6 | Implemented | UI labels generated output and advises checking the source report; Qwen3 Apache 2.0 credit is in `docs/architecture.md`; the chatbot performs no automated business action. |

## External setup still required

- Provision local/server `.env`, `APP_KEY`, strong DB credentials, domain, and persistent Docker host.
- Download the selected Ollama model and run the chatbot evaluation set on the target server hardware.
- Register the protected self-hosted deployment runner and set GitHub branch protection/environment settings.
- Configure DNS, Caddy TLS reachability or Cloudflare Tunnel, and email SMTP if outbound mail is required.
- Capture Flutter 360 px accessibility/responsiveness evidence and complete API/resource coverage to close the partial requirements above.
