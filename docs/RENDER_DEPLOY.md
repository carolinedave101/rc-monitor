# Render free deployment plan

Status: **live** at https://rc-monitor.onrender.com (deployed commit `ef6d589`); demo login
`test@example.com` / `password` and the admin area were verified. The free instance sleeps
after ~15 min idle, and the first visit takes ~30–60 s to wake and re-seed. Keep the steps
below for redeploys.

Note: production seeding uses the model factories, which call `fake()`. Laravel only
defines `fake()` when `fakerphp/faker` is installed, so it lives in `require` (not
`require-dev`) — otherwise `composer install --no-dev` breaks `migrate --seed`.

## Prerequisite

The repo is **private**, so Render's “Public Git repository” option reports
“Repository not found”. Either:

- connect GitHub in Render: **New + → Web Service → GitHub** → authorize the Render GitHub
  App and pick `carolinedave101/rc-monitor` (private repos work on the free plan), or
- make the repo public first: GitHub → repo **Settings → General → Danger Zone → Change
  visibility → Make public**.

## Option A — manual web service (recommended)

1. Sign up at https://render.com (GitHub sign-in works).
2. **New +** → **Web Service** → **GitHub** → authorize the Render GitHub App and select
   `carolinedave101/rc-monitor` (use **Public Git repository** instead if you made it
   public).
3. Settings:
   - Language/Runtime: **Docker**
   - Instance Type: **Free**
   - Health Check Path: `/up`
4. **Create Web Service** and wait for the build. The URL is
   `https://rc-monitor.onrender.com` (Render appends a suffix if the name is taken).

## Option B — Blueprint (render.yaml)

**New +** → **Blueprint** → connect the repo → Render reads `render.yaml` and creates the
same free Docker web service.

## How config is handled

- The Dockerfile already sets `APP_ENV`, `APP_DEBUG`, `SESSION_DRIVER`, `CACHE_STORE`,
  `QUEUE_CONNECTION`, `DB_CONNECTION`, `MAIL_MAILER`, `LOG_CHANNEL`, SQLite WAL and
  `PHP_CLI_SERVER_WORKERS`.
- `docker/entrypoint.sh` generates `APP_KEY` on boot when it is not set and derives
  `APP_URL` from Render's `RENDER_EXTERNAL_URL`.
- Render injects `PORT`; the entrypoint serves on `${PORT:-7860}`.
- Setting a real `APP_KEY` (Render dashboard → Environment) is optional; sessions and DB
  reset on every boot anyway.

## Free-tier behaviour

- The service sleeps after ~15 minutes without traffic; the next visit takes ~30 s to
  wake and re-seed.
- No persistent disk: DB, uploads and backups reset on every wake/redeploy. Demo only.
- The scheduler (`simulate:devices` every minute) runs while the service is awake.
- Builds take a few minutes (apt + Composer install).

## Verify

- Log in with `test@example.com` / `password`
- Dashboard device status polls every 15 s
- Admin → Simulation → “Tick now” generates simulated activity
- `GET /up` returns healthy
