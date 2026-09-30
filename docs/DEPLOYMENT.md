# Deployment guide

This guide covers running ROYALTRICO in production. The app is a standard Laravel 13
application with a database-backed queue/cache/session and a scheduled simulator.

## 1. Requirements

- PHP 8.3+ with `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`,
  `openssl`, `pcre`, `pdo_mysql`/`pdo_pgsql`, `session`, `tokenizer`, `xml`, `zip`
- Composer 2, Node.js 20+ (build-time only)
- MySQL 8 / MariaDB / PostgreSQL (SQLite works for pilots)
- A web server (nginx/Apache) pointing at `public/`

## 2. Environment

Copy `.env.example` to `.env` and set at minimum:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=            # php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=royaltrico
DB_USERNAME=royaltrico
DB_PASSWORD=change-me

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp         # notifications are sent by mail
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=alerts@your-domain.example
```

Never run production with `APP_DEBUG=true`; stack traces can leak secrets.

## 3. Build and migrate

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force            # first deploy only: features, plans, payment methods
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ensure `storage/` and `bootstrap/cache/` are writable by the web user.

## 4. Scheduler

The simulator (`simulate:devices`, every minute) and backups (`backup:database`, daily at
02:00) are scheduled in `routes/console.php`. Run exactly one scheduler:

```bash
# Option A: cron
* * * * * cd /var/www/rc-monitor && php artisan schedule:run >> /dev/null 2>&1

# Option B: supervisor/systemd
php artisan schedule:work
```

## 5. Queue

Alert notifications are sent synchronously today, but the queue is configured and ready.
If you add queued jobs/notifications, run a worker under supervisor/systemd:

```bash
php artisan queue:work --tries=3 --timeout=90
```

Monitor failures on **Admin → System** (failed jobs counter) and `php artisan queue:failed`.

## 6. Backups

`backup:database` writes dumps to `storage/app/private/backups` (SQLite file copy;
`mysqldump`/`pg_dump` for MySQL/PostgreSQL) and keeps the latest 7 by default. The same
actions are available in **Admin → System** with manual runs and downloads.

Copy backups off the server regularly (object storage, rsync, etc.) and test a restore:
`mysql -u user -p db < storage/app/private/backups/db-YYYYmmdd-HHMMSS.sql`.

## 7. Security checklist

- HTTPS everywhere (the app trusts proxy headers only if configured; see `bootstrap/app.php`)
- `APP_DEBUG=false`, strong `APP_KEY` (regenerate only when rotating)
- Agent API is rate-limited (120 requests/minute per token) and every token can be rotated
  from the device page or **Admin → Devices**
- Payment proofs are stored privately and served only to admins through authenticated routes
- Run `composer audit` and `composer update` regularly; re-run the test suite after updates
- Rotate `agent_token`s if a device is lost; suspend the device to stop ingestion immediately
- Keep `php artisan about` and **Admin → System** green as part of routine monitoring

## 8. Health and monitoring

- `GET /up` — framework health endpoint for uptime checks
- **Admin → System** — environment, queue state, failed jobs, backup list
- **Admin dashboard** — device/alert/payment counters and the audit feed

## 9. Zero-downtime tips

- Deploy code, run `migrate --force` (migrations are additive), then reload PHP-FPM
- Re-run `config:cache route:cache view:cache` after every deploy
- Pause the scheduler during large migrations if needed (`Settings` global simulation pause
  is available in **Admin → Simulation**)
