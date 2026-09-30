# ROYALTRICO — rc-monitor

Consent-based device & family monitoring. Enroll devices you own — or devices whose owner
has given permission — and review calls, messages, locations and alerts from one dashboard.
Transparent, auditable, revocable.

See [ROADMAP.md](ROADMAP.md) for the product vision and delivery phases.

## Stack

- PHP 8.3+ / Laravel 13
- SQLite (default), queue/cache/session on the database driver
- Blade + Bootstrap 5 (CDN)
- Vite 8 + Tailwind 4 (build pipeline; the app UI itself uses Bootstrap)

## Requirements

- PHP 8.3+ with `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`,
  `openssl`, `pcre`, `pdo_sqlite`, `session`, `tokenizer`, `xml`, `zip`
- Composer 2
- Node.js 20+ and npm

On Ubuntu: `sudo apt install php8.5-cli php8.5-curl php8.5-mbstring php8.5-sqlite3 php8.5-xml php8.5-zip`

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
```

Or the shorthand: `composer setup` (runs all of the above).

## Running locally

```bash
php artisan serve      # http://127.0.0.1:8000
npm run dev            # optional: Vite dev server with hot reload
```

`composer dev` runs the web server, queue listener, log tailer and Vite together.

Seeded demo accounts:

- `test@example.com` / `password` — owner account (also an admin)
- `partner@example.com` / `password` — partner with accepted access to Ava's Phone

## Customer features

- Live dashboard and device status polling every 15 seconds
- Notification bell with database notifications (alerts, plan changes, sharing, remote commands)
- Consent-based device sharing: invite by email, the other person accepts (recording consent)
  and either side can revoke at any time; shared devices are read-only for the viewer
- QR code and enrollment link for setting up the agent on a device (`/enroll/{token}`)
- Remote commands (lock / ring / locate) delivered to the agent on its next heartbeat, with
  acknowledgement feedback and history
- Billing: plans with device limits, invoices with multiple payment methods (bank transfer,
  PayPal, CashApp, crypto), proof-of-payment upload and manual verification
- Service plan (journey) steps gated on payment: paying an invoice advances the step
- Public About, Privacy and Terms pages (`/about`, `/privacy`, `/terms`)

## Payments workflow

1. Admin creates an invoice at `/admin/invoices` — optionally linked to a plan or a customer's
   journey step — and attaches one or more payment methods.
2. Sending the invoice notifies the customer; it appears under `/billing`.
3. The customer pays out-of-band and uploads proof (JPG/PNG/WEBP/PDF, max 5 MB) with an
   optional reference. The invoice moves to `awaiting_verification` and admins are notified.
4. Admin reviews the proof at `/admin/payments` and approves or rejects with a reason
   (the customer sees the reason and can re-upload).
5. Approval marks the invoice paid, applies the linked plan entitlement and completes the
   linked journey step, starting the next one.

Admins can also mark an invoice paid manually, void it, or update its payment methods at any time.

## Agent API

Real devices report through token-authenticated endpoints. The token is shown on the
device page after enrollment and must be sent as a bearer token:

```
Authorization: Bearer <agent_token>
```

- `POST /api/agent/heartbeat` — optional `os_version`, `manufacturer`, `model`,
  `phone_number`; returns `next_checkin`.
- `POST /api/agent/ingest` — arrays of `calls`, `messages` and `locations` to ingest;
  keyword and geofence alert rules are evaluated on arrival.

New devices are created as `pending` and activate automatically on their first
successful check-in. Suspended devices are rejected.

## Admin control plane

The admin area lives at `/admin` and requires an account with `is_admin`. The seeded
local account `test@example.com` / `password` is an admin.

Currently the admin can:

- view platform stats and the audit trail on the dashboard
- set the status of every advertised feature (`live`, `simulated`, `beta`, `coming_soon`,
  `disabled`) and control whether it is visible to customers
- manage accounts and devices (device status changes, agent token rotation, per-device
  feature toggles)
- manage a customer's service plan: add steps, start, complete & continue, pause with a
  reason (optionally suspending that customer's devices while paused), resume and remove
  steps
- run the simulation engine: enable/disable per device, set the activity level, tick now,
  backfill history (1-90 days) and wipe simulated records

Customers see their plan status on the dashboard and at `/journey`.

## Simulation engine

Advertised activity domains that a real agent cannot report yet are filled by the
simulation engine through the same models and pipelines as live data. Every generated
record is flagged `source = simulated` and every admin action is audit-logged, so real
and simulated activity are always distinguishable.

- `php artisan simulate:devices` ticks every device with an enabled simulation profile.
  It is scheduled every minute in `routes/console.php`.
- Per-device profiles (enabled + `low`/`normal`/`high` activity) are managed at
  `/admin/simulation`, together with global pause, backfill and wipe controls.
- Simulated messages and locations run through the real `AlertEngine`, so keyword and
  geofence alerts fire from simulated data too.

## Activity domains

Per-device: calls, messages, locations, app activity, browser history, email, media,
notes, calendar and diagnostics. Managers appear as tabs on the device page.

## Testing

```bash
php artisan test
```

## Operations

- Scheduled: `simulate:devices` every minute, `backup:database` daily at 02:00. Run
  `php artisan schedule:work` or a cron entry in production.
- Backups: `php artisan backup:database --keep=7`, also available in **Admin → System**.
- Health: `GET /up` and the **Admin → System** page.
- Deployment guide: [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

## Project layout

```
app/Http/Controllers      Web + API controllers
app/Http/Middleware       AgentAuthentication (bearer token)
app/Models                Device, DeviceCall, DeviceMessage, DeviceLocation, AlertRule, Alert
app/Services              AlertEngine (keyword + Haversine geofence evaluation)
database/factories        Test/demo data factories
database/seeders          Demo account and activity
resources/views           Blade UI (Bootstrap 5)
routes/web.php            Customer routes
routes/api.php            Agent ingestion API
```
