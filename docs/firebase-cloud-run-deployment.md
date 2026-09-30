# Firebase Hosting + Cloud Run Deployment

Firebase Hosting serves the Vite assets and rewrites application requests to
Laravel on Cloud Run. Laravel connects directly to Supabase PostgreSQL. The
Firebase project is `rgv-system`; the Hosting site is
`rgv-multi-tech-services`.

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