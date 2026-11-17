# Deployment, rollback, and database recovery

## Initial deployment

1. Install Docker Engine and the Compose v2 plugin on the Linux host. Configure the DNS name in `APP_DOMAIN` and make ports 80/443 reachable, or configure an operator-managed Cloudflare Tunnel to route the hostname to Caddy.
2. Clone the repository on the host. Copy `.env.example` to `.env`; set a unique `APP_KEY`, production `APP_URL`/`APP_DOMAIN`, and `APP_ENV=production`, `APP_DEBUG=false`. Set PostgreSQL host, port, database, username, and password from Supabase Dashboard > Connect. The example uses the shared Session pooler for IPv4 compatibility; use the direct endpoint only on IPv6-capable hosts. Keep the password in the untracked `.env` or deployment secret store. TLS is required by default.
3. Start the services and run only additive migrations:

   ```bash
   docker compose up -d --build
   docker compose exec app php artisan migrate --force
   docker compose exec ollama ollama pull qwen3:1.7b
   curl --fail https://$APP_DOMAIN/api/health
   ```

4. Apply [`database/supabase/mobile_backend.sql`](../database/supabase/mobile_backend.sql) once as the Supabase database owner after migrations. Set Laravel's `SUPABASE_URL`, `SUPABASE_PUBLISHABLE_KEY`, and server-only `SUPABASE_SECRET_KEY` so the admin UI can provision employee Auth identities. Older projects can use the legacy `SUPABASE_SERVICE_ROLE_KEY` JWT. The native app uses only the public URL and publishable key as build-time Dart defines; it does not require the mobile service or Laravel to be running.
5. The first model download may take several minutes. If Ollama is stopped or the model is missing, the web app remains available and the chatbot returns a clear local-model-unavailable message.

Application storage, log, model, and Caddy data volumes survive normal rebuilds. Only Caddy publishes a port; Supabase PostgreSQL is reached over TLS and is not exposed through this Compose stack.

## CI/CD and access controls

`.github/workflows/ci-cd.yml` runs backend checks, frontend production build, Flutter checks, and legacy mobile-service tests against a disposable PostgreSQL instance on PRs to `main`, pushes to `main`, and manual dispatch. Deployment runs only after all test jobs pass on a push to `main` and only on the self-hosted runner labeled `rgv-production`; pull requests and manual dispatches do not deploy. The native app's runtime does not depend on that optional service.

The deployment host must have Docker Engine and Compose v2, outbound HTTPS access to GitHub, and a persistent checkout/runner service. Register the GitHub Actions runner under an unprivileged deployment account that can use Docker, with labels `self-hosted`, `linux`, and `rgv-production`. The Node 24 based checkout/setup actions require Actions Runner `v2.327.1` or later. Install it as a service and confirm it returns to `Idle` after a host reboot. Do not put the one-time runner registration token, private SSH keys, or `.env` in the repository.

On GitHub, create the `production` environment and set its `APP_DOMAIN` variable. Protect `main` with pull requests, block force pushes, and require the `backend`, `frontend`, and `mobile` checks. Keep deployment restricted to trusted maintainers. PR workflows run on GitHub-hosted runners; deployment is limited to a successful push on `main`, so untrusted fork code never runs on the production runner. For a public repository, also require approval for workflows from external contributors under Actions settings.

The app image is tagged `rgv-system-app:<commit-sha>` during deployment. Application files, logs, Ollama models, and Caddy state use named volumes. Deployment concurrency prevents overlapping releases. See [the CI/CD runbook](cicd-runbook.md) for pipeline verification, release evidence, and recovery steps.

## Rollback

1. Identify the last known healthy commit and restore its source with a reviewed revert commit or `git checkout` on the deployment host.
2. Preferred: create a revert branch/commit, open a PR, wait for the required CI checks, and merge. The main workflow will build and deploy the revert, then verify its new commit ID at `/api/health`.
3. Do not run down migrations automatically. Database migrations must remain backward-compatible; if a schema rollback is needed, take a new backup and use a reviewed, migration-specific recovery plan.
4. Verify `/api/health`, deployed commit, login, and a read-only workflow. Preserve all named volumes.

For an emergency app-only rollback, first confirm the previous commit image still exists with `docker image ls rgv-system-app`. From the same deployment checkout and Compose project used by the runner, set `APP_COMMIT` to the previous healthy image tag and run `docker compose up -d --no-build app`. Verify `/api/health` reports that selected version. This does not roll back database migrations; use it only when the prior app version remains compatible with the current schema. Then revert the bad Git commit through the normal PR path so a later deployment does not reintroduce it.

## PostgreSQL backup

Create the backup directory on a protected host and use the `pg_dump` version supported by the Supabase PostgreSQL server. Use the dashboard's direct endpoint for backup tooling, and store connection settings in environment variables or a protected password file (never in shell history or command arguments). Write a compressed logical dump outside the application volume:

```bash
mkdir -p backups
pg_dump --host="$DB_HOST" --port="$DB_PORT" --username="$DB_USERNAME" --dbname="$DB_DATABASE" --format=custom --no-owner --no-privileges > "backups/rgv-$(date -u +%Y%m%dT%H%M%SZ).dump"
```

Restrict backup permissions and copy backups to a separately protected location. The dump contains personal and operational data. Back up `app_storage` separately when it contains user uploads or backup artifacts; discover the exact Compose volume name with `docker volume ls` and archive it read-only. Do not put backups in Git or expose them under `public/`.

## Restore and verification

Restore into a separately provisioned empty Supabase project/database first, then validate schema, users, role links, foreign keys, row counts, and representative workflows before changing deployment configuration. The restore target must be explicitly approved and must not be the active production database.

```bash
pg_restore --host="$RESTORE_DB_HOST" --port="$RESTORE_DB_PORT" --username="$RESTORE_DB_USERNAME" --dbname="$RESTORE_DB_NAME" --no-owner --no-privileges < backups/rgv-YYYYMMDDTHHMMSSZ.dump
psql --host="$RESTORE_DB_HOST" --port="$RESTORE_DB_PORT" --username="$RESTORE_DB_USERNAME" --dbname="$RESTORE_DB_NAME" -c 'select count(*) from users;'
```

Do not restore over an existing production database until an independently verified backup exists and a maintenance window is approved. This procedure is documented but was not run against the user's database during this task.
