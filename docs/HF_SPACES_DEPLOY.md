# Hugging Face Spaces deployment plan

Status: local deployment files are **done and tested**; the Space itself has **not been
created or pushed** yet. Resume from “Remaining steps” below.

## What is already done

- [x] `Dockerfile` — `php:8.5-cli` (lock file needs PHP >= 8.4.1 for Symfony 8.1),
      mbstring/zip/pdo_sqlite, `composer install --no-dev`, exposes port 7860
- [x] `docker/entrypoint.sh` — creates SQLite, `migrate --force --seed`, auto-generates
      `APP_KEY` if unset, runs `artisan serve` + `artisan schedule:work` together
- [x] `.dockerignore`
- [x] `README.md` — Hugging Face frontmatter (`sdk: docker`, `app_port: 7860`)
- [x] `bootstrap/app.php` — `trustProxies(at: '*')` so HTTPS works behind the HF proxy
- [x] `config/database.php` — SQLite `busy_timeout` / `journal_mode` read from env
      (Docker sets `DB_BUSY_TIMEOUT=5000`, `DB_JOURNAL_MODE=wal` for multi-worker safety)
- [x] `docs/DEPLOYMENT.md` section 10 — short version of this guide
- [x] Verified: `php artisan test` 162/162 passed, `bash -n docker/entrypoint.sh`, Pint
      clean on touched files

The changes are not committed yet.

## Remaining steps

1. Generate a key and copy it:

   ```bash
   php artisan key:generate --show
   ```

2. Create the Space: https://huggingface.co/new-space — name `rc-monitor`, SDK
   **Docker**, Blank template, **Public**.

3. In the Space's **Settings → Variables and secrets**, add:

   - Secret `APP_KEY` = the key from step 1
   - Variable `APP_URL` = `https://<user>-rc-monitor.hf.space`
   - Variables `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_DRIVER=database`,
     `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `DB_CONNECTION=sqlite`,
     `MAIL_MAILER=log`

4. Commit and push:

   ```bash
   git add -A && git commit -m "chore: add Docker deployment for Hugging Face Spaces"
   git remote add hf https://huggingface.co/spaces/<user>/rc-monitor
   git push hf main    # username + HF write token as the password
   ```

   (If the remote already exists: `git remote set-url hf <url>`.)

5. Watch the **Build logs** tab on the Space page. Once it finishes, the app is live at
   `https://<user>-rc-monitor.hf.space`.

## Verify after the build

- Log in with `test@example.com` / `password` (seeded on every boot)
- Dashboard device status polls every 15 s
- Admin → Simulation → “Tick now” generates simulated activity
- `GET /up` returns healthy

## Caveats (free tier)

- Ephemeral filesystem: DB, uploads and backups reset on sleep, restart or rebuild.
  Demo only, never real data.
- Free Spaces sleep after inactivity; the first visit takes ~30 s to wake and re-seed.
- The simulator only runs while the Space is awake.
- Seeded demo credentials are public knowledge — fine for a demo, not for private data.
- Persistence + no-sleep require the paid tier.
