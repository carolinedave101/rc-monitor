# Hugging Face Spaces deployment plan

Status: local deployment files are **done, tested and committed** (`b1efe43`). The
Hugging Face Space **`Carolinedave101/royal` exists but is private and has no code
pushed** yet. Resume from “Remaining steps” below.

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

The Docker changes were committed in `b1efe43`; they are not pushed to GitHub yet.

## Remaining steps

1. In the Space **`Carolinedave101/royal`** → **Settings → Variables and secrets**, add:

   - Secret `APP_KEY` = get one with `php artisan key:generate --show`
   - Variable `APP_URL` = `https://carolinedave101-royal.hf.space`
   - Variables `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_DRIVER=database`,
     `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `DB_CONNECTION=sqlite`,
     `MAIL_MAILER=log`

2. Make the Space public: **Settings → Change visibility → Public** (it is currently
   private, so the link is not shareable yet).

3. Push the code (the `hf` remote is already configured):

   ```bash
   git push hf main    # username Carolinedave101 + HF write token as the password
   ```

   Optionally sync GitHub too: `git push origin main` (local `main` is 1 commit ahead).

4. Watch the **Build logs** tab on the Space page. Once it finishes, the app is live at
   `https://carolinedave101-royal.hf.space`.

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
