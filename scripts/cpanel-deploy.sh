#!/usr/bin/env bash
# Shared cPanel auto-deploy for Vehicle Wash Manager
# Safe for cron: exits quickly when remote has no new commits.
#
# Usage (from project root — same folder as artisan):
#   chmod +x scripts/cpanel-deploy.sh
#   cp .env.deploy.example .env.deploy   # optional overrides
#   bash scripts/cpanel-deploy.sh
#
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$APP_DIR"

# ---------------------------------------------------------------------------
# Load optional .env.deploy (KEY=VALUE, no export needed)
# ---------------------------------------------------------------------------
if [ -f "$APP_DIR/.env.deploy" ]; then
  # shellcheck disable=SC1091
  set -a
  # shellcheck disable=SC1090
  source "$APP_DIR/.env.deploy"
  set +a
fi

DEPLOY_BRANCH="${DEPLOY_BRANCH:-cursor/vehicle-wash-phase1-7c7f}"
DEPLOY_PHP="${DEPLOY_PHP:-php}"
DEPLOY_RUN_SEEDER="${DEPLOY_RUN_SEEDER:-0}"
DEPLOY_REMOTE="${DEPLOY_REMOTE:-origin}"
DEPLOY_FORCE="${DEPLOY_FORCE:-0}"   # 1 = deploy even if no new commits
LOG_KEEP_DAYS="${DEPLOY_LOG_KEEP_DAYS:-30}"

# Composer: prefer explicit DEPLOY_COMPOSER, else ./composer.phar, else composer
if [ -n "${DEPLOY_COMPOSER:-}" ]; then
  COMPOSER_CMD=( $DEPLOY_COMPOSER )
elif [ -f "$APP_DIR/composer.phar" ]; then
  COMPOSER_CMD=( "$DEPLOY_PHP" "$APP_DIR/composer.phar" )
elif command -v composer >/dev/null 2>&1; then
  COMPOSER_CMD=( composer )
else
  COMPOSER_CMD=()
fi

# ---------------------------------------------------------------------------
# Logging — one folder per day under storage/logs/cpanel-deploy/YYYY-MM-DD/
# ---------------------------------------------------------------------------
DAY="$(date +%Y-%m-%d)"
LOG_ROOT="$APP_DIR/storage/logs/cpanel-deploy"
LOG_DIR="$LOG_ROOT/$DAY"
mkdir -p "$LOG_DIR"
LOG_FILE="$LOG_DIR/deploy.log"

# Prune old day folders
find "$LOG_ROOT" -mindepth 1 -maxdepth 1 -type d -mtime +"$LOG_KEEP_DAYS" -exec rm -rf {} + 2>/dev/null || true

log() {
  local msg="[$(date '+%Y-%m-%d %H:%M:%S')] $*"
  echo "$msg" | tee -a "$LOG_FILE"
}

fail() {
  log "ERROR: $*"
  exit 1
}

log "======== cpanel-deploy start ========"
log "dir=$APP_DIR branch=$DEPLOY_BRANCH php=$DEPLOY_PHP"

# ---------------------------------------------------------------------------
# Preconditions
# ---------------------------------------------------------------------------
[ -f "$APP_DIR/artisan" ] || fail "artisan not found — run from project root"
[ -f "$APP_DIR/.env" ] || fail ".env missing — copy deploy/cpanel/.env.cpanel.example and configure"
command -v "$DEPLOY_PHP" >/dev/null 2>&1 || fail "PHP not found: $DEPLOY_PHP"
[ ${#COMPOSER_CMD[@]} -gt 0 ] || fail "composer not found — place composer.phar in project root or set DEPLOY_COMPOSER"

# Dirty tree blocks auto-deploy (unless DEPLOY_FORCE=1 with clean intent — still refuse dirty)
if [ -n "$(git status --porcelain 2>/dev/null)" ]; then
  log "Working tree is dirty. Auto-deploy refused."
  log "Fix with: git status && git checkout -- .   OR   git stash"
  log "Then re-run: bash scripts/cpanel-deploy.sh"
  exit 0
fi

# ---------------------------------------------------------------------------
# Git fetch / compare / pull
# ---------------------------------------------------------------------------
git fetch "$DEPLOY_REMOTE" "$DEPLOY_BRANCH" 2>&1 | tee -a "$LOG_FILE" || fail "git fetch failed (check credentials)"

LOCAL_SHA="$(git rev-parse HEAD)"
REMOTE_SHA="$(git rev-parse "$DEPLOY_REMOTE/$DEPLOY_BRANCH" 2>/dev/null || true)"

if [ -z "$REMOTE_SHA" ]; then
  fail "Remote branch $DEPLOY_REMOTE/$DEPLOY_BRANCH not found"
fi

log "local=$LOCAL_SHA"
log "remote=$REMOTE_SHA"

if [ "$LOCAL_SHA" = "$REMOTE_SHA" ] && [ "$DEPLOY_FORCE" != "1" ]; then
  log "No new commits — nothing to deploy."
  log "======== cpanel-deploy end (noop) ========"
  exit 0
fi

CURRENT_BRANCH="$(git rev-parse --abbrev-ref HEAD)"
if [ "$CURRENT_BRANCH" != "$DEPLOY_BRANCH" ]; then
  log "Checking out $DEPLOY_BRANCH (was $CURRENT_BRANCH)"
  git checkout "$DEPLOY_BRANCH" 2>&1 | tee -a "$LOG_FILE" || fail "git checkout failed"
fi

log "Pulling $DEPLOY_REMOTE/$DEPLOY_BRANCH (ff-only)"
git pull --ff-only "$DEPLOY_REMOTE" "$DEPLOY_BRANCH" 2>&1 | tee -a "$LOG_FILE" || fail "git pull --ff-only failed"

# ---------------------------------------------------------------------------
# Build / migrate / cache
# ---------------------------------------------------------------------------
log "Maintenance mode ON"
"$DEPLOY_PHP" artisan down --retry=60 >>"$LOG_FILE" 2>&1 || true

log "composer install --no-dev"
"${COMPOSER_CMD[@]}" install --no-dev --optimize-autoloader --no-interaction 2>&1 | tee -a "$LOG_FILE" \
  || { "$DEPLOY_PHP" artisan up >>"$LOG_FILE" 2>&1 || true; fail "composer install failed"; }

log "migrate --force"
"$DEPLOY_PHP" artisan migrate --force 2>&1 | tee -a "$LOG_FILE" \
  || { "$DEPLOY_PHP" artisan up >>"$LOG_FILE" 2>&1 || true; fail "migrate failed"; }

if [ "$DEPLOY_RUN_SEEDER" = "1" ]; then
  log "db:seed --force (DEPLOY_RUN_SEEDER=1)"
  "$DEPLOY_PHP" artisan db:seed --force 2>&1 | tee -a "$LOG_FILE" || log "WARN: seeder failed (continuing)"
fi

log "storage:link"
"$DEPLOY_PHP" artisan storage:link >>"$LOG_FILE" 2>&1 || true

log "optimize caches"
"$DEPLOY_PHP" artisan optimize:clear >>"$LOG_FILE" 2>&1 || true
"$DEPLOY_PHP" artisan config:cache >>"$LOG_FILE" 2>&1 || true
"$DEPLOY_PHP" artisan route:cache >>"$LOG_FILE" 2>&1 || true
"$DEPLOY_PHP" artisan view:cache >>"$LOG_FILE" 2>&1 || true
"$DEPLOY_PHP" artisan filament:assets >>"$LOG_FILE" 2>&1 || true

log "permissions storage + bootstrap/cache"
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

log "Maintenance mode OFF"
"$DEPLOY_PHP" artisan up >>"$LOG_FILE" 2>&1 || true

log "Deploy finished OK (was $LOCAL_SHA → now $(git rev-parse HEAD))"
log "======== cpanel-deploy end ========"
exit 0
