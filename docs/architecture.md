# Architecture and runtime services

## Request paths

```text
Browser (SPA at /app) --HTTPS--> Cloudflare Tunnel --> Caddy --handle_path /app/*--> React SPA (nginx)
Browser (portal)     --------------------------------> Caddy --catch-all--------> Laravel 12
Flutter app --HTTPS + Bearer token--> /api/v1/mobile/* ----------------------> Laravel 12
Laravel --private network--> MySQL 8.4 (db-data volume)
Laravel --private network--> Ollama (report assistant)
```

The reverse proxy (`deploy/Caddyfile`) is the only service that publishes host
ports. It strips and forwards `/app/*` to the React SPA and forwards every
other path to the Laravel container, so each pre-existing Laravel URL keeps its
original owner. The browser never reaches the database or Ollama directly.

Laravel is the single source of identity and data access. The Flutter app
authenticates against Laravel and uses only `/api/v1/mobile`; it holds no
database credentials or service keys.

## Repository boundaries

- `Backend/` is the complete Laravel application (REST API + Blade staff
  portal, config, routes, views, tests, migrations).
- `Frontend/app/` is the REST-only React + TypeScript client served at `/app`.
- `Frontend/web/` holds the Vite asset source for the Blade portal and compiles
  into `Backend/public/build`.
- `Frontend/mobile/` is the Flutter Android employee client.
- `docs/` contains the documentation set, diagrams, evaluation instruments and
  the OpenAPI specification.
- `deploy/` contains the Caddy configuration and the SPA nginx configuration;
  `docker/` contains the container entrypoint.

## Main components

- `Backend/app/Http/Controllers/Api/V1/` implements the versioned REST
  resources (inventories, borrow requests, inventory categories, bookings, auth).
- `Backend/app/Http/Middleware/EnsureApiRole.php` is the token-safe role guard
  for API routes; `IsAdmin`/`IsEmployee` remain session guards for the portal.
- `Backend/app/Http/Controllers/Api/V1/Concerns/PaginatesResources.php` provides
  the shared `page`/`limit`/`sort`/`q` + `meta` list contract.
- `Backend/bootstrap/app.php` renders the uniform
  `{"error": {"code", "message", "details"}}` envelope for `/api/v1`.
- `Backend/app/Support/HealthProbe.php` backs `GET /api/health`.
- `Backend/app/Support/SqlDialect.php` centralises the few engine-specific date
  expressions so MySQL and the SQLite test suite never drift apart.
- Domain workflows such as borrowing and stock reservation live in
  `Backend/app/Services/BorrowService.php` and are reused by the v1 API rather
  than reimplemented.
- `Backend/app/Models/` and `Backend/database/migrations/` define the relational
  schema. The audit checksum chain is serialised with a MySQL named lock.
- `Backend/app/Services/DatabaseRetrievalService.php` runs allow-listed report
  aggregates for the admin chatbot; it does not accept SQL from a model.
- `Backend/app/Services/OllamaService.php` calls the configured local Ollama
  host. There is no hosted AI fallback.
- `Frontend/app/src/lib/api.ts` is the single network layer for the SPA.
- `Frontend/mobile/lib/core/network/mobile_api_client.dart` is the single
  network layer for the Flutter app; it targets `/api/v1/mobile` only.
- Mobile reads and writes are online-only: if the API is unreachable the
  operation fails visibly and is not treated as saved.
- `compose.yaml` defines `db`, `app`, `web`, `caddy`, `ollama` and an optional
  `tunnel`. Only Caddy publishes 80/443; database data persists in `db-data`.

## Runtime dependency choices

| Service | Runtime choice |
|---|---|
| Relational data | MySQL 8.4 container (`db-data` volume). A managed MySQL/MariaDB endpoint can be substituted with `DB_HOST`. |
| Web client | React 18 + TypeScript static build served by nginx in the `web` service |
| Staff portal | Laravel Blade rendered by the `app` service |
| Mobile authentication | Laravel Sanctum bearer tokens; Laravel users + Spatie roles are the source of identity |
| AI inference | Private Ollama 0.40.16 service, default model `qwen3:1.7b` (Apache 2.0) |
| Files/backups | Laravel local private storage on a persistent volume |
| HTTP/TLS | Caddy reverse proxy; no public database, Laravel or Ollama ports |
| Email | Laravel mail configuration, local log driver by default |

Laravel owns authentication end to end. Borrow and return retries use shared
idempotency tables, and scanned QR tokens resolve against current inventory
rows. See [mobile-api-boundary.md](mobile-api-boundary.md).

## Data privacy for chatbot requests

Chatbot input is not stored in plaintext. A SHA-256 question reference, user
ID, selected report, aggregate source reference, response, model name, result
and elapsed milliseconds are written to `chatbot_interactions`.

Record questions (a named person, a date range, returned items, pending tasks)
are resolved by `Chatbot/DatabaseAssistant` and formatted by
`Chatbot/AnswerComposer` on the server. Those answers come straight from
database rows and never pass through the model. When an open-ended question
needs the local Ollama model, the prompt contains aggregates plus non-personal
reference, status and date fields from recent records; names, addresses,
emails, passwords, tokens and unrestricted records are not sent to the model.
Interaction history is not shown to other users.

Model credit: Qwen3 1.7B from the Qwen team, distributed under Apache License
2.0. Ollama is pinned to release 0.40.16. This is a small CPU-friendly
starting point; actual latency and answer quality depend on deployment
hardware and remain subject to documented evaluation.
