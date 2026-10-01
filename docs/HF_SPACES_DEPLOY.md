# Hugging Face Spaces deployment plan

Status (Oct 2026): the Docker files are pushed to the Space `Carolinedave101/royal`, it is
public and configured (`APP_KEY`, `APP_URL`), but it **cannot start on the free tier**.
Hugging Face removed free CPU-basic compute for Docker/Gradio Spaces in 2026, so the
runtime reports `Quota exceeded for flavor cpu-basic (requested=1): current=0, limit=0`.
Only static Spaces are free now; Docker Spaces require a paid plan. Resume from
“Free alternatives” below unless upgrading.

## What is already done

- [x] `Dockerfile` — `php:8.5-cli` (lock file needs PHP >= 8.4.1 for Symfony 8.1),
      mbstring/zip/pdo_sqlite, `composer install --no-dev`, exposes port 7860 and honours
      `$PORT`, so it also works on Render/Koyeb
- [x] `docker/entrypoint.sh` — creates SQLite, `migrate --force --seed`, auto-generates
      `APP_KEY` if unset, runs `artisan serve` + `artisan schedule:work` together
- [x] `.dockerignore`
- [x] `README.md` — Hugging Face frontmatter (`sdk: docker`, `app_port: 7860`)
- [x] `bootstrap/app.php` — `trustProxies(at: '*')` so HTTPS works behind proxies
- [x] `config/database.php` — SQLite `busy_timeout` / `journal_mode` read from env
      (Docker sets `DB_BUSY_TIMEOUT=5000`, `DB_JOURNAL_MODE=wal` for multi-worker safety)
- [x] `docs/DEPLOYMENT.md` section 10 — short version of this guide
- [x] Verified: `php artisan test` 162/162 passed, `bash -n docker/entrypoint.sh`, Pint
      clean on touched files
- [x] Space `Carolinedave101/royal` created, made public, `APP_KEY` secret and `APP_URL`
      variable set through the API
- [x] HF scaffold merged and code pushed (`08441c3` on `hf/main`)

Local `main` is 3 commits ahead of GitHub (`origin/main`) — push with `git push origin main`.

## Blocked by Hugging Face policy

- `GET /api/spaces/Carolinedave101/royal` → `runtime.errorMessage`:
  `Quota exceeded for flavor cpu-basic (requested=1): current=0, limit=0`
- HF docs (spaces-gpus): “CPU Basic has no hourly cost, but creating a new Space that runs
  on compute (Gradio or Docker) requires a paid plan. Static Spaces are free for
  everyone.”
- Pausing other Spaces does not help: the only other Space (`Carolinedave101/al`) is
  static and cannot be paused.

## Free alternatives (pick one)

1. **Render free web service** — closest drop-in: connect the GitHub repo, runtime Docker,
   Render injects `$PORT` (entrypoint already honours it) and serves
   `https://<name>.onrender.com`. Free instance sleeps after ~15 min idle with an
   ephemeral disk (demo data resets on wake/redeploy), and the entrypoint runs the
   scheduler while awake.
2. **Google Cloud e2-micro Always Free VM** — most reliable forever-free option. Needs a
   Google account with a card for verification, ~30–45 min setup (Docker or nginx + PHP,
   plus cron for `schedule:run`).
3. **Oracle Cloud Always Free VM** — same idea, ARM or AMD micro instance.
4. **Cloudflare quick tunnel** — instant public link from your own machine: run
   `php artisan serve` and `cloudflared tunnel --url http://localhost:8000`; the link only
   lives while the machine and tunnel are up.
5. **Upgrade Hugging Face** — PRO ($9/mo) or pay-as-you-go CPU ($0.03/h) makes the pushed
   Space run as-is.

## If you upgrade Hugging Face later

- The code is already pushed; press **Factory rebuild** in the Space settings (or
  `POST /api/spaces/Carolinedave101/royal/restart`) and it should build and run.
- Keep the `APP_KEY` secret and `APP_URL=https://carolinedave101-royal.hf.space`.
- Verify: login `test@example.com` / `password`, Admin → Simulation → “Tick now”, `GET /up`.

## Original HF setup (for reference)

1. Generate a key: `php artisan key:generate --show`.
2. Space → **Settings → Variables and secrets**: secret `APP_KEY`, variable
   `APP_URL=https://<user>-<space>.hf.space`, plus `APP_ENV=production`,
   `APP_DEBUG=false`, `SESSION_DRIVER=database`, `CACHE_STORE=database`,
   `QUEUE_CONNECTION=database`, `DB_CONNECTION=sqlite`, `MAIL_MAILER=log`.
3. Make the Space public: **Settings → Change visibility → Public**.
4. Push: `git push hf main` (username + HF write token as the password).

## Verify after any deploy

- Log in with `test@example.com` / `password` (seeded on every boot)
- Dashboard device status polls every 15 s
- Admin → Simulation → “Tick now” generates simulated activity
- `GET /up` returns healthy

## Caveats (free tiers)

- Ephemeral filesystem: DB, uploads and backups reset on sleep, restart or rebuild.
  Demo only, never real data.
- Free instances sleep after inactivity; the first visit takes ~30 s to wake and re-seed.
- The simulator only runs while the instance is awake.
- Seeded demo credentials are public knowledge — fine for a demo, not for private data.
