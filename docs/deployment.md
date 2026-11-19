# Deployment, rollback, and database recovery

The stack is defined by `compose.yaml`: `db` (MySQL 8.4), `app` (Laravel),
`web` (React SPA on nginx), `caddy` (reverse proxy), `ollama`, and an optional
`tunnel` (Cloudflare).

## Initial deployment

1. Install Docker Engine and Docker Compose on a Linux host.

2. Clone the repository. Copy `.env.example` to `.env` at the repository root
   (Compose reads this file for variable interpolation) and copy
   `Backend/.env.example` to `Backend/.env` for the application itself.

3. In both files set a unique `APP_KEY`, `APP_ENV=production`,
   `APP_DEBUG=false`, the production `APP_URL`/`APP_DOMAIN`, and the MySQL
   connection values (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`,
   `DB_PASSWORD`). Leave `DB_HOST` empty to use the bundled `db` container.
   Keep real values out of version control.

4. Start the stack and prepare the database:

   ```bash
   docker compose up -d --build
   docker compose exec -T app php artisan key:generate --force
   docker compose exec -T app php artisan migrate --force
   docker compose exec -T app php artisan db:seed --force
   docker compose exec -T ollama ollama pull qwen3:1.7b
   ```

5. Verify:

   ```bash
   curl -fs http://localhost/api/health
   curl -fs http://localhost/api/docs -o /dev/null
   ```

`/api/health` must report `status: ok`, `database: ok`, and the deployed
commit.

6. The system is then reachable at `http://localhost` (staff portal) and
   `http://localhost/app/` (web client). For a real hostname, point DNS at the
   server; Caddy obtains a certificate automatically.

Only Caddy publishes ports (80/443). MySQL and Ollama are reachable only
inside the Compose network.

## Environment variables (names only)

Root `.env` (Compose interpolation): `APP_DOMAIN`, `APP_COMMIT`, `DB_DATABASE`,
`DB_USERNAME`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`, `DB_PORT`, `DB_HOST`,
`FRONTEND_URL`, `OLLAMA_BASE_URL`, `TUNNEL_TOKEN`.

`Backend/.env` (application): `APP_*`, `DB_*`, `SESSION_*`, `CACHE_STORE`,
`QUEUE_CONNECTION`, `MAIL_*`, `OLLAMA_*`, `FRONTEND_URL`, `APP_VERSION`,
`APP_COMMIT`, `SANCTUM_*`. The complete list with placeholders is in
`Backend/.env.example`.

## CI/CD and access controls

`.github/workflows/ci-cd.yml` runs on pull requests to `main`, pushes to
`main`, and manual dispatch:

- `backend` - PHP syntax, Pint on the API modules, PHPUnit with a coverage gate.
- `frontend-spa` - ESLint, Vitest, production build.
- `frontend-assets` and `mobile` - asset build and Flutter analyze/test.
- `compose` - Compose configuration validation and container build.
- `migrate-check` - `migrate` and `db:seed` against a real MySQL service, so
  schema problems cannot pass on SQLite alone.

Deployment runs only on a push to `main` after every job passes, on the
self-hosted runner labelled `rgv-production`. It rebuilds images, runs
migrations, then requires `/api/health` to report `status: ok` and the new
commit SHA before succeeding.

## Rollback

Images are tagged with the commit SHA (`rgv-system-app:${APP_COMMIT}`). To
roll back, check out the previous commit and redeploy, or pin `APP_COMMIT` to
the last known-good SHA and run:

```bash
docker compose up -d --build
```

Migrations are additive; a rollback of application code does not require
reverting the schema.

## MySQL backup

Write dumps outside the container and outside the application volume:

```bash
mkdir -p ~/backups
docker compose exec -T db mysqldump \
  --single-transaction --routines --events --no-tablespaces \
  -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" \
  > ~/backups/rgv-$(date -u +%Y%m%dT%H%M%SZ).sql
```

`--single-transaction` gives a consistent snapshot for InnoDB without locking
the tables. Copy dumps off the host. A backup step also runs in the deploy
workflow.

## Restore and verification

Restore into a **separate** empty database first; never restore over the live
database:

```bash
docker compose exec -T db mysql \
  -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < ~/backups/rgv-2026-11-20.sql
```

Validate before switching any configuration:

```bash
docker compose exec -T db mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" \
  -e "SELECT COUNT(*) FROM users; SELECT COUNT(*) FROM inventories;"
```

Then check schema parity, role links, foreign keys, row counts and a
representative workflow. Record the date, the dump file and the row counts
checked.

The admin UI also exposes an import/restore action backed by
`DatabaseBackupRestoreService`, which shells out to the `mysql` client.

## Monitoring

- `/api/health` reports status, version, commit and database connectivity.
- Application logs are written to the `app_logs` volume.
- Request logging records method, path, status, user and duration; bodies and
  tokens are never logged.

## Known issues

- Docker Compose, Caddy routing and the public HTTPS address have not been
  executed on the development machine (Docker is not installed there).
- A backup restore drill must still be performed and recorded on the deployed
  database.
