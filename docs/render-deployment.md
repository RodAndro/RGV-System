# Free Render Deployment

This deploys Laravel as a Docker web service on Render and keeps Supabase as the
PostgreSQL database. Firebase Hosting and Cloud Run are not used for serving the
Laravel app. The Firebase SDK remains in the frontend for Analytics.

Render currently offers a free web-service plan, but describes free instances
as suitable for testing and personal projects rather than production. A free
service sleeps after 15 minutes without traffic, may take about a minute to
wake, has 512 MB RAM and 750 service hours per workspace per month, and has an
ephemeral filesystem. See [Render's free instance limits](https://render.com/docs/free).

## Deploy

1. Push this repository to a Git provider supported by Render.
2. In the [Render Dashboard](https://dashboard.render.com), create a new
   **Blueprint** from the repository. Render will read `render.yaml` and create
   the `rgv-system` web service on the Free plan in Singapore.
3. When prompted, set `APP_KEY`, `DB_HOST`, `DB_USERNAME`, and `DB_PASSWORD`.
   Generate the Laravel key locally with `php artisan key:generate --show`.
   Use the Supabase session-pooler host and username from your own
   `.env.example` values/project settings; keep the password in Render's
   environment settings, not in Git.
4. Confirm the Blueprint, wait for the first build and pre-deploy migration to
   complete, then open the `*.onrender.com` URL shown in the Render Dashboard.

The Supabase database needs to be reachable from Render. The Blueprint uses
PostgreSQL over TLS on port `5432`. The service URL is supplied to Laravel as
`APP_URL`; do not point the app at the Firebase Hosting URL.

## Free-plan tradeoffs

- Files uploaded to Laravel's local disk are lost when the service restarts,
  redeploys, or sleeps. Configure external object storage before keeping
  booking attachments, avatars, inventory photos, or return evidence.
- There is no always-running queue worker in this Blueprint. `QUEUE_CONNECTION`
  is set to `sync`, so queued work runs during the web request and may make
  imports/exports slow or hit request limits.
- Free services can be restarted and may be suspended after exceeding plan
  limits. Keep database backups independently in Supabase.
- The free service may sleep and cold-start, so the first request after idle
  can take about a minute.

The Render Blueprint runs `php artisan migrate --force` before each deploy. It
does not run the project seeders; the full seeder creates demo accounts with
known passwords.