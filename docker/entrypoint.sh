#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
    APP_KEY="base64:$(head -c 32 /dev/urandom | base64 | tr -d '\n')"
    export APP_KEY
    echo "APP_KEY=${APP_KEY}" >> .env
fi

touch database/database.sqlite

php artisan migrate --force --seed

php artisan serve --host=0.0.0.0 --port="${PORT:-7860}" &
serve_pid=$!

php artisan schedule:work &
schedule_pid=$!

trap 'kill "$serve_pid" "$schedule_pid" 2>/dev/null || true' TERM INT

wait -n "$serve_pid" "$schedule_pid"
