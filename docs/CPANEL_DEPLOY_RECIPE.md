# Automate updates on shared cPanel — Vehicle Wash Manager

Domain: **https://vehiclewash.cpanel.site**

You can run `git pull` in Terminal. On shared hosting, the most reliable automation is a **deploy script + cron** (runs every few minutes and updates when GitHub has new commits).

---

## Recommended: cron + deploy script

### 1. One-time setup on cPanel

In Terminal (SSH), from your **project folder** (not `public/`):

```bash
cd ~/vehicle-wash-manager   # <- your real path (same folder as artisan)
git checkout cursor/vehicle-wash-phase1-7c7f   # or main after merge
chmod +x scripts/cpanel-deploy.sh
cp .env.deploy.example .env.deploy
```

Edit `.env.deploy` if needed:

```bash
DEPLOY_BRANCH=cursor/vehicle-wash-phase1-7c7f
DEPLOY_PHP=php
DEPLOY_RUN_SEEDER=0
```

Leave `DEPLOY_COMPOSER` unset — the script auto-uses `./composer.phar`.  
If you set it yourself, quote values with spaces:

```bash
DEPLOY_COMPOSER="php composer.phar"
```

Also ensure app `.env` exists (first install):

```bash
cp deploy/cpanel/.env.cpanel.example .env
# edit DB, APP_URL=https://vehiclewash.cpanel.site, mail
# then: php artisan key:generate && composer install --no-dev
# then: php artisan migrate --force
```

Test once:

```bash
bash scripts/cpanel-deploy.sh
# Today’s log (one folder per day):
tail -50 storage/logs/cpanel-deploy/$(date +%Y-%m-%d)/deploy.log
ls storage/logs/cpanel-deploy/
```

### 2. Make GitHub auth non-interactive

Cron cannot type a password. Use a Personal Access Token (or SSH deploy key) once:

```bash
# HTTPS + token (stored by git credential helper)
git config --global credential.helper store
git pull origin cursor/vehicle-wash-phase1-7c7f
# Username: your GitHub username
# Password: paste a classic PAT with `repo` scope
```

Or switch the remote to SSH if your host supports it.

### 3. Add a cron job in cPanel

cPanel → **Cron Jobs** → every **5 minutes** (or 15):

```bash
cd /home/YOUR_USER/vehicle-wash-manager && /bin/bash scripts/cpanel-deploy.sh >/dev/null 2>&1
```

Replace the path with the real project root (same folder as `artisan`).

Also keep the Laravel scheduler (every minute) for renewals + daily email:

```bash
* * * * * cd /home/YOUR_USER/vehicle-wash-manager && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

### What the script does when the branch changed

1. `git fetch` + `git pull --ff-only`
2. `composer install --no-dev`
3. `php artisan migrate --force`
4. Clear/rebuild caches (+ Filament assets)
5. Logs under `storage/logs/cpanel-deploy/YYYY-MM-DD/deploy.log`  
   (one folder per day; old folders auto-deleted after 30 days)

If nothing new is on GitHub, it exits quickly and does nothing.

### 4. Keep the working tree clean

Auto-deploy **stops** if you edited files on the server. Prefer changing code only via GitHub. If stuck:

```bash
git status
git checkout -- .          # discard local file edits (careful!)
# or: git stash
bash scripts/cpanel-deploy.sh
```

Never put secrets only on the server inside tracked files; keep `.env` and `.env.deploy` untracked.

---

## Alternative A: pull only when you push (GitHub webhook)

Use this if you want immediate deploy after push, and your host allows `shell_exec` / `exec` from PHP (many shared hosts disable this — then stick to cron).

1. Create a long random secret.
2. Add to **server** `.env` (and optionally `.env.deploy`):

```bash
DEPLOY_WEBHOOK_SECRET=change-me-to-a-long-random-string
```

3. In GitHub → repo → Settings → Webhooks → Add webhook:
   - **Payload URL:** `https://vehiclewash.cpanel.site/deploy/webhook`
   - **Content type:** `application/json`
   - **Secret:** same as `DEPLOY_WEBHOOK_SECRET`
   - **Event:** Just the push event
   - **Active:** yes

4. Push to the deploy branch → webhook hits the app → runs `scripts/cpanel-deploy.sh`.

If the webhook returns 500 / empty, PHP likely cannot run shell commands → use cron instead.

---

## Alternative B: cPanel “Git Version Control”

Some hosts include Git Version Control with a deploy button/hook.

1. cPanel → Git Version Control → clone/pull your repo
2. Set document root to the app’s `public/` folder
3. Add a deploy hook that calls:

```bash
bash scripts/cpanel-deploy.sh
```

This is fine if your host provides it; **cron still works everywhere Terminal works**.

---

## What not to rely on (shared hosting)

| Method | Why it’s often blocked |
|--------|-------------------------|
| GitHub Actions → SSH into cPanel | SSH keys / inbound SSH limited |
| Always-on queue worker / Horizon | No long-running processes |
| Watching filesystem | No supervisor |

---

## Quick checklist

- [ ] Project is a git clone on the deploy branch
- [ ] `credential.helper store` or SSH key works without prompts
- [ ] `bash scripts/cpanel-deploy.sh` succeeds once by hand
- [ ] Cron job points at the project root
- [ ] Document root is still `public/`
- [ ] Check `storage/logs/cpanel-deploy/YYYY-MM-DD/deploy.log` after the next GitHub push

---

## Related files

| File | Purpose |
|------|---------|
| `scripts/cpanel-deploy.sh` | Main auto-deploy script |
| `.env.deploy.example` | Deploy overrides template |
| `deploy/cpanel/setup-first.sh` | First-time install helper |
| `deploy/cpanel/.env.cpanel.example` | App production `.env` template |
| `deploy/cpanel/cron.txt` | Cron snippets |
| `app/Http/Controllers/DeployWebhookController.php` | Optional GitHub webhook |
