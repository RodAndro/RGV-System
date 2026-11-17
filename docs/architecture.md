# Architecture and runtime services

## Request paths

```text
Browser ──HTTPS──> Caddy ──private network──> Laravel 12 ──PostgreSQL──┐
Flutter ──Supabase Auth + restricted RPCs───────────────────────────────┼──> Supabase
Laravel Auth Admin API ─────────────────────────────────────────────────┘
Laravel chatbot / reports ───────────────────────────> Ollama (private network)
```

Laravel serves the web application and provisions employee identities in Supabase Auth with a server-only key. Flutter uses Supabase Auth for sign-in and restricted database RPCs for mobile workflows. Both operate independently and share the same Supabase project. The optional `mobile-service/` code is not part of the native app runtime.

## Main components

- `app/Http/Controllers/` contains web/API controllers; domain workflows such as borrowing and stock reservation remain in `app/Services/BorrowService.php`.
- `app/Models/` and `database/migrations/` define relational data. Schema changes in this migration add chatbot trace records without modifying existing rows.
- `app/Services/DatabaseRetrievalService.php` runs allow-listed report aggregates for the admin chatbot. It does not accept SQL from a model.
- `app/Services/OllamaService.php` calls the configured local Ollama host. There is no hosted AI fallback.
- `database/supabase/mobile_backend.sql` links confirmed Auth identities to active employee rows and restricts mobile access to reviewed RPCs.
- `mobile/lib/core/network/` maps app workflows to Supabase RPCs; `supabase_flutter` manages Auth sessions.
- Mobile business-data reads and writes are online-only. The app does not cache business records or queue transactions; if Supabase is unreachable, the operation fails visibly and is not treated as saved.
- `storage/app/private` is the private local filesystem root. Compose persists `storage/app` and keeps the public document tree separate.
- `compose.yaml` defines Laravel, Ollama, and Caddy. Only Caddy publishes 80/443. A deployment may route public HTTPS through Caddy directly or via an operator-managed HTTPS tunnel; Supabase PostgreSQL remains the only relational database.

## Runtime dependency choices

| Service | Runtime choice |
|---|---|
| Relational data | Supabase-hosted PostgreSQL shared by Laravel and Flutter |
| Mobile authentication | Supabase Auth identities linked to Laravel employee rows and Spatie roles |
| AI inference | Private Ollama 0.40.16 service, default model `qwen3:1.7b` (Apache 2.0; multilingual support includes Tagalog) |
| Files/backups | Laravel local private storage on a persistent volume |
| HTTP/TLS | Caddy reverse proxy; no public database, Laravel, or Ollama ports |
| Email | Laravel mail configuration, local log driver by default |

Laravel uses its server-side PostgreSQL configuration and a server-only Supabase Auth secret. Flutter uses only the public project URL and publishable key; no PostgreSQL credentials or privileged keys are included in the APK. Borrow and return retries use shared idempotency tables, and scanned QR tokens resolve against current inventory rows. See [mobile-supabase-boundary.md](mobile-supabase-boundary.md) for account linking and setup.

## Data privacy for chatbot requests

Chatbot input is not stored in plaintext. A SHA-256 question reference, user ID, selected report, aggregate source reference, response, model name, result, and elapsed milliseconds are written to `chatbot_interactions`. Retrieved data is aggregate-only; no names, addresses, email addresses, passwords, tokens, or unrestricted records are sent to the local model. Interaction history is not shown to other users.

Model credit: Qwen3 1.7B from the Qwen team, distributed under Apache License 2.0. Ollama is pinned to release 0.40.16. This is a small CPU-friendly starting point; actual latency and answer quality depend on deployment hardware and remain subject to the documented evaluation.
