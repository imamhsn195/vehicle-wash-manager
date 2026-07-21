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

# Preserve CLI/cron overrides so .env.deploy cannot clobber them
# (e.g. DEPLOY_FORCE=1 bash scripts/cpanel-deploy.sh)
_CLI_DEPLOY_BRANCH="${DEPLOY_BRANCH-}"
_CLI_DEPLOY_PHP="${DEPLOY_PHP-}"
_CLI_DEPLOY_RUN_SEEDER="${DEPLOY_RUN_SEEDER-}"
_CLI_DEPLOY_REMOTE="${DEPLOY_REMOTE-}"
_CLI_DEPLOY_FORCE="${DEPLOY_FORCE-}"
_CLI_DEPLOY_COMPOSER="${DEPLOY_COMPOSER-}"
_CLI_DEPLOY_LOG_KEEP_DAYS="${DEPLOY_LOG_KEEP_DAYS-}"

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

[ -n "$_CLI_DEPLOY_BRANCH" ] && DEPLOY_BRANCH="$_CLI_DEPLOY_BRANCH"
[ -n "$_CLI_DEPLOY_PHP" ] && DEPLOY_PHP="$_CLI_DEPLOY_PHP"
[ -n "$_CLI_DEPLOY_RUN_SEEDER" ] && DEPLOY_RUN_SEEDER="$_CLI_DEPLOY_RUN_SEEDER"
[ -n "$_CLI_DEPLOY_REMOTE" ] && DEPLOY_REMOTE="$_CLI_DEPLOY_REMOTE"
[ -n "$_CLI_DEPLOY_FORCE" ] && DEPLOY_FORCE="$_CLI_DEPLOY_FORCE"
[ -n "$_CLI_DEPLOY_COMPOSER" ] && DEPLOY_COMPOSER="$_CLI_DEPLOY_COMPOSER"
[ -n "$_CLI_DEPLOY_LOG_KEEP_DAYS" ] && DEPLOY_LOG_KEEP_DAYS="$_CLI_DEPLOY_LOG_KEEP_DAYS"

DEPLOY_BRANCH="${DEPLOY_BRANCH:-main}"
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

# Ignore permission-only diffs (chmod on shared hosting dirties tracked .gitignore files)
git config core.fileMode false >/dev/null 2>&1 || true

# Soft-clean noise that shared hosting / prior deploys leave behind
reset_deploy_noise() {
  rm -f "$APP_DIR/error_log" "$APP_DIR/public/error_log" "$APP_DIR/composer-setup.php" 2>/dev/null || true
  find "$APP_DIR" -name error_log -type f -not -path '*/vendor/*' -not -path '*/.git/*' -delete 2>/dev/null || true
  # Filament assets are committed in git — discard accidental republish diffs
  git checkout -- \
    public/js/filament \
    public/css/filament \
    bootstrap/cache/.gitignore \
    storage/app/.gitignore \
    storage/app/private/.gitignore \
    storage/app/public/.gitignore \
    storage/framework/.gitignore \
    storage/framework/cache/.gitignore \
    storage/framework/cache/data/.gitignore \
    storage/framework/sessions/.gitignore \
    storage/framework/testing/.gitignore \
    storage/framework/views/.gitignore \
    storage/logs/.gitignore \
    2>/dev/null || true
}

reset_deploy_noise

# Dirty tree blocks auto-deploy (real local edits only)
if [ -n "$(git status --porcelain 2>/dev/null)" ]; then
  log "Working tree is dirty. Auto-deploy refused."
  log "Dirty paths:"
  git status --porcelain 2>/dev/null | while IFS= read -r line; do log "  $line"; done
  log "Fix with: git status && git checkout -- . && git clean -fd -e vendor -e .env -e .env.deploy -e composer.phar -e storage"
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
  # Still apply any pending migrations (e.g. code was pulled by hand earlier)
  PENDING="$("$DEPLOY_PHP" artisan migrate:status 2>/dev/null | grep -c 'Pending' || true)"
  if [ "${PENDING:-0}" -gt 0 ]; then
    log "No new commits, but $PENDING pending migration(s) — applying"
    "$DEPLOY_PHP" artisan migrate --force 2>&1 | tee -a "$LOG_FILE" \
      || fail "migrate failed"
    "$DEPLOY_PHP" artisan config:cache >>"$LOG_FILE" 2>&1 || true
    log "Pending migrations applied."
  else
    log "No new commits — nothing to deploy."
  fi
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

log "composer dump-autoload -o"
"${COMPOSER_CMD[@]}" dump-autoload -o --no-interaction 2>&1 | tee -a "$LOG_FILE" || true

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
# filament assets are committed in the repo — do not republish (dirtifies working tree)

log "permissions storage + bootstrap/cache (dirs/writable files only)"
find storage bootstrap/cache -type d -exec chmod ug+rwx {} + 2>/dev/null || true
find storage bootstrap/cache -type f ! -name '.gitignore' -exec chmod ug+rw {} + 2>/dev/null || true

log "Maintenance mode OFF"
"$DEPLOY_PHP" artisan up >>"$LOG_FILE" 2>&1 || true

# Leave tree clean for the next cron tick
reset_deploy_noise

log "Deploy finished OK (was $LOCAL_SHA → now $(git rev-parse HEAD))"
log "======== cpanel-deploy end ========"
exit 0
