# Firebase Hosting + Cloud Run Deployment

Firebase Hosting serves the Vite assets and rewrites website requests to
Laravel on Cloud Run. Laravel connects directly to Supabase PostgreSQL. The
Flutter Android app uses Supabase Auth and restricted database RPCs, so mobile
employee workflows do not route through Cloud Run. The Firebase project
is `rgv-system`; the Hosting site is `rgv-multi-tech-services`.

## Billing note

Google Cloud requires a billing account to run Cloud Run, even when usage stays
within its free quota. Usage beyond free allowances can be charged. Enabling
APIs alone does not deploy or run a service. Review the current [Cloud Run
pricing](https://cloud.google.com/run/pricing) and confirm billing is set up
before creating the service.

## Enable required APIs

For project number `686696003488`, enable Cloud Run, Cloud Build, Artifact
Registry, and Secret Manager APIs in the Google Cloud Console, or run:

```powershell
gcloud config set project rgv-system
gcloud services enable run.googleapis.com cloudbuild.googleapis.com artifactregistry.googleapis.com secretmanager.googleapis.com
```

The Cloud Run service and Firebase Hosting rewrite must use the same region and
service name: `asia-southeast1` and `rgv-system`.

## Prepare secrets

Generate the Laravel application key with `php artisan key:generate --show`.
In Secret Manager, create `rgv-app-key` and `rgv-supabase-password`, then add
their secret values. Use the Supabase pooler host, username, and database
password from your own Supabase project settings. Never put the database
password or Laravel key in Git.

Grant the Cloud Run runtime service account the Secret Manager Secret Accessor
role for both secrets.

## Build and deploy the backend

From the repository root, after installing and authenticating the Google Cloud
CLI and confirming billing is enabled:

```powershell
$ProjectId = "rgv-system"
$ProjectNumber = "686696003488"
$Region = "asia-southeast1"
$Image = "$Region-docker.pkg.dev/$ProjectId/rgv/rgv-system"
$RuntimeServiceAccount = "$ProjectNumber-compute@developer.gserviceaccount.com"

gcloud artifacts repositories create rgv --repository-format=docker --location=$Region
gcloud builds submit --tag $Image

$RuntimeEnv = "APP_ENV=production,APP_DEBUG=false,APP_URL=https://rgv-multi-tech-services.web.app,LOG_CHANNEL=stderr,LOG_LEVEL=info,DB_CONNECTION=pgsql,DB_HOST=YOUR_SUPABASE_POOLER_HOST,DB_PORT=5432,DB_DATABASE=postgres,DB_USERNAME=postgres.YOUR_SUPABASE_PROJECT_REF,DB_SSLMODE=require,SESSION_DRIVER=database,CACHE_STORE=database,QUEUE_CONNECTION=sync,FILESYSTEM_DISK=local"
$RuntimeSecrets = "APP_KEY=rgv-app-key:latest,DB_PASSWORD=rgv-supabase-password:latest"

gcloud run deploy rgv-system `
  --image $Image `
  --region $Region `
  --service-account $RuntimeServiceAccount `
  --port 8080 `
  --allow-unauthenticated `
  --min 0 `
  --max 1 `
  --set-env-vars $RuntimeEnv `
  --set-secrets $RuntimeSecrets
```

Replace the Supabase placeholders with the actual connection values. Run
migrations once from an authenticated Cloud Shell or deployment environment
that has the same database variables and secrets:

```powershell
php artisan migrate --force
```

After migrations, run `database/supabase/mobile_backend.sql` in the Supabase SQL
Editor. Invite existing active employees through Supabase Auth using their
Laravel email addresses and require email confirmation. Employees set a new
Supabase password from the invite; the SQL trigger links the confirmed identity
and mirrors its bcrypt password hash to the Laravel employee row. Employees
whose Laravel account requires MFA are prompted to enroll a Supabase TOTP factor
in the app. Flutter must use only the Supabase URL and publishable key, never
the service-role key.

Build the Android app with the Supabase project values:

```powershell
Set-Location mobile
flutter build apk --release `
  --dart-define=SUPABASE_URL=https://YOUR_PROJECT_REF.supabase.co `
  --dart-define=SUPABASE_ANON_KEY=YOUR_PUBLISHABLE_KEY
```

The website and mobile app share PostgreSQL records. Loading the web request
page after Cloud Run recovers reads those rows directly; no synchronization
worker is needed. Mobile writes remain available while Cloud Run is down as
long as Supabase Auth and PostgreSQL are reachable.

Do not run the full project seeder in production; it creates demo users with
known passwords. Set `QUEUE_CONNECTION=sync` until a separate worker is
deployed, so queued operations do not remain unprocessed.

## Deploy Firebase Hosting

The Hosting site and target are already configured in `.firebaserc` and
`firebase.json`. After the Cloud Run service is live and healthy:

```powershell
npm ci
npm run build
firebase deploy --only hosting:rgv-multi-tech-services --project rgv-system
```

The site will be available at
<https://rgv-multi-tech-services.web.app>. Uploaded files still need durable
object storage; the container filesystem is not persistent. Keep database
credentials and other secrets in Secret Manager, not in the frontend.