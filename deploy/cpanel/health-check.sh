#!/usr/bin/env bash
# Quick health check after deploy
# Usage: ./deploy/cpanel/health-check.sh
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$APP_DIR"

PHP_BIN="${PHP_BIN:-php}"
URL="${APP_URL:-https://vehiclewash.cpanel.site}"

echo "==> Health check: $APP_DIR"
echo ""

ok() { echo "  [OK] $1"; }
fail() { echo "  [FAIL] $1"; FAILED=1; }

FAILED=0

[ -f .env ] && ok ".env exists" || fail ".env missing"
[ -d vendor ] && ok "vendor/ installed" || fail "vendor/ missing — run composer install"
[ -d storage/logs ] && ok "storage/logs writable check" || fail "storage/logs missing"

"$PHP_BIN" artisan about >/dev/null 2>&1 && ok "artisan boots" || fail "artisan failed"

"$PHP_BIN" artisan migrate:status >/dev/null 2>&1 && ok "DB connection + migrations" || fail "DB / migrations problem"

if command -v curl >/dev/null 2>&1; then
  CODE=$(curl -s -o /dev/null -w "%{http_code}" "$URL/admin/login" || echo "000")
  if [ "$CODE" = "200" ] || [ "$CODE" = "302" ]; then
    ok "HTTP $CODE $URL/admin/login"
  else
    fail "HTTP $CODE $URL/admin/login (check docroot / SSL)"
  fi
fi

echo ""
if [ "${FAILED:-0}" = "1" ]; then
  echo "Health check FAILED — see messages above"
  exit 1
fi
echo "Health check PASSED"
