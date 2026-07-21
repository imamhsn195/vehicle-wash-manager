#!/usr/bin/env bash
# One command: pull latest + deploy
# Usage: ./deploy/cpanel/pull-and-deploy.sh [branch]
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
BRANCH="${1:-cursor/vehicle-wash-phase1-7c7f}"
cd "$APP_DIR"

echo "==> git fetch + checkout $BRANCH"
git fetch origin
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

./deploy/cpanel/deploy.sh
./deploy/cpanel/health-check.sh || true
