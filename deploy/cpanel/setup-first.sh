#!/usr/bin/env bash
# First-time cPanel setup for Vehicle Wash Manager
# Usage: ./deploy/cpanel/setup-first.sh
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$APP_DIR"

echo "==> App directory: $APP_DIR"

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  echo "ERROR: php not found. Set PHP_BIN=/full/path/to/php"
  exit 1
fi

PHP_VERSION="$($PHP_BIN -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
echo "==> PHP version: $PHP_VERSION"
"$PHP_BIN" -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);' \
  || { echo "ERROR: PHP 8.2+ required"; exit 1; }

if [ ! -f .env ]; then
  if [ -f deploy/cpanel/.env.cpanel.example ]; then
    cp deploy/cpanel/.env.cpanel.example .env
    echo "==> Created .env from .env.cpanel.example — EDIT IT NOW (DB, URL, mail)"
  else
    cp .env.example .env
    echo "==> Created .env from .env.example — EDIT IT NOW"
  fi
  echo "    nano .env"
  echo "    Then re-run: ./deploy/cpanel/setup-first.sh"
  exit 0
fi

# Ensure APP_KEY exists
if ! grep -q '^APP_KEY=base64:' .env; then
  echo "==> Generating APP_KEY"
  "$PHP_BIN" artisan key:generate --force
fi

echo "==> Composer install (no-dev)"
if command -v "$COMPOSER_BIN" >/dev/null 2>&1; then
  "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction
else
  echo "WARN: composer not in PATH. Run composer install manually, then re-run this script."
  exit 1
fi

echo "==> Storage link"
"$PHP_BIN" artisan storage:link 2>/dev/null || true

echo "==> Migrate database"
"$PHP_BIN" artisan migrate --force

read -r -p "Seed demo data? (y/N) " SEED
if [[ "${SEED:-N}" =~ ^[Yy]$ ]]; then
  "$PHP_BIN" artisan db:seed --force
  echo "==> Demo login: admin@carwash.test / password  (CHANGE THIS)"
fi

echo "==> Permissions"
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

echo "==> Optimize"
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan filament:assets 2>/dev/null || true

echo ""
echo "==> First-time setup complete."
echo "    1. Point vehiclewash.cpanel.site document root to:"
echo "       $APP_DIR/public"
echo "    2. Add cron jobs from: deploy/cpanel/cron.txt"
echo "    3. Visit: https://vehiclewash.cpanel.site/admin"
echo ""
