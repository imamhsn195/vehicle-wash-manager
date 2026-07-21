#!/usr/bin/env bash
# Deploy / update on cPanel after git pull
# Usage: cd ~/vehicle-wash-manager && ./deploy/cpanel/deploy.sh
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$APP_DIR"

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

echo "==> Deploying: $APP_DIR"
echo "==> $(date -u '+%Y-%m-%d %H:%M:%S UTC')"

if [ ! -f .env ]; then
  echo "ERROR: .env missing. Run setup-first.sh first."
  exit 1
fi

echo "==> Maintenance mode ON"
"$PHP_BIN" artisan down --retry=60 || true

echo "==> Composer install"
"$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction

echo "==> Migrate"
"$PHP_BIN" artisan migrate --force

echo "==> Storage link"
"$PHP_BIN" artisan storage:link 2>/dev/null || true

echo "==> Clear & rebuild caches"
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan filament:assets 2>/dev/null || true

echo "==> Permissions"
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

echo "==> Maintenance mode OFF"
"$PHP_BIN" artisan up

echo "==> Deploy finished OK"
"$PHP_BIN" artisan about --only=environment 2>/dev/null || true
