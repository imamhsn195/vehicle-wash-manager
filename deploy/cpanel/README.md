# cPanel Deployment — vehiclewash.cpanel.site

> **Primary guide:** [docs/CPANEL_DEPLOY_RECIPE.md](../../docs/CPANEL_DEPLOY_RECIPE.md)  
> **Auto-deploy script:** `scripts/cpanel-deploy.sh` (cron every 5 min)

This folder has first-install helpers. Day-to-day updates use the cron deploy script.

## Quick start

```bash
cd ~/vehicle-wash-manager
git checkout cursor/vehicle-wash-phase1-7c7f
chmod +x scripts/cpanel-deploy.sh deploy/cpanel/*.sh

# App env (once)
cp deploy/cpanel/.env.cpanel.example .env
# edit .env → DB + APP_URL=https://vehiclewash.cpanel.site
./deploy/cpanel/setup-first.sh

# Deploy config (once)
cp .env.deploy.example .env.deploy
bash scripts/cpanel-deploy.sh

# Cron (cPanel → Cron Jobs) — see cron.txt or CPANEL_DEPLOY_RECIPE.md
```

Document root → `.../vehicle-wash-manager/public`

## Scripts in this folder

| Script | When |
|--------|------|
| `setup-first.sh` | First install only |
| `deploy.sh` | Manual full deploy (no git pull) |
| `health-check.sh` | After deploy |
| `pull-and-deploy.sh` | Manual pull + deploy |

For **automated** updates, prefer `scripts/cpanel-deploy.sh` + cron (skips work when nothing changed).
