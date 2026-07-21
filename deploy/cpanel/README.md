# cPanel Deployment — vehiclewash.cpanel.site

Automation and checklist for deploying Vehicle Wash Manager on cPanel.

## Recommended directory layout

```
/home/YOUR_CPANEL_USER/
├── vehicle-wash-manager/          ← git clone here (app root)
│   ├── app/
│   ├── artisan
│   ├── .env                       ← production secrets (never commit)
│   ├── public/                    ← Laravel public folder
│   └── deploy/
│       └── cpanel/
│           ├── deploy.sh          ← run after each git pull
│           ├── setup-first.sh     ← first-time setup
│           ├── cron.txt           ← copy into cPanel Cron Jobs
│           └── .env.cpanel.example
└── public_html/                   ← OR subdomain folder
    └── vehiclewash/               ← document root points here
        └── (symlink to ../vehicle-wash-manager/public)
```

### Preferred: subdomain document root → `public`

In cPanel → **Domains** → `vehiclewash.cpanel.site` → Document Root:

```
/home/YOUR_CPANEL_USER/vehicle-wash-manager/public
```

If the host won’t allow that, symlink instead:

```bash
# from public_html or subdomain folder
rm -rf vehiclewash   # only if empty/placeholder
ln -s /home/YOUR_CPANEL_USER/vehicle-wash-manager/public vehiclewash
```

---

## First-time setup (once)

### 1. Create MySQL database (cPanel → MySQL Databases)

| Item | Example |
|------|---------|
| Database | `user_vehiclewash` |
| User | `user_vwapp` |
| Password | (strong password) |
| Privileges | ALL |

### 2. Clone the repo (SSH or Git Version Control)

```bash
cd ~
git clone https://github.com/imamhsn195/vehicle-wash-manager.git vehicle-wash-manager
cd vehicle-wash-manager
git checkout cursor/vehicle-wash-phase1-7c7f   # or main after merge
```

### 3. Run first-time setup script

```bash
chmod +x deploy/cpanel/*.sh
./deploy/cpanel/setup-first.sh
```

Or manually:

```bash
cp deploy/cpanel/.env.cpanel.example .env
# edit .env with DB + APP_URL + mail
php -d detect_unicode=0 $(which composer) install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force   # optional: demo data
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Point domain to `public`

Document root = `.../vehicle-wash-manager/public`

### 5. Add cron jobs

cPanel → **Cron Jobs** → paste from `deploy/cpanel/cron.txt`

### 6. Open the site

https://vehiclewash.cpanel.site/admin

Demo (if seeded): `admin@carwash.test` / `password` — **change immediately**.

---

## Every update (after you pull)

```bash
cd ~/vehicle-wash-manager
git pull
./deploy/cpanel/deploy.sh
```

`deploy.sh` will:

1. `composer install --no-dev`
2. `php artisan migrate --force`
3. Clear & rebuild caches
4. `storage:link` (if missing)
5. Set safe permissions on `storage` and `bootstrap/cache`

---

## PHP requirements (cPanel MultiPHP)

| Extension | Required |
|-----------|----------|
| PHP | **8.2+** (8.3 preferred) |
| `pdo_mysql` | yes |
| `mbstring` | yes |
| `openssl` | yes |
| `tokenizer` | yes |
| `xml` | yes |
| `ctype` | yes |
| `json` | yes |
| `bcmath` | yes |
| `fileinfo` | yes |
| `gd` | yes (Excel exports) |
| `zip` | yes |

Set PHP version for the domain in **MultiPHP Manager**.

---

## Cron jobs (copy into cPanel)

See `cron.txt`. Minimum:

```
* * * * * cd /home/YOUR_CPANEL_USER/vehicle-wash-manager && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Laravel schedule already includes:

- `08:00` — flag contract renewals  
- `21:00` — email daily wash summary to admins  

---

## Mail (daily summary)

In `.env` use cPanel SMTP or the hosting mail account:

```
MAIL_MAILER=smtp
MAIL_HOST=mail.vehiclewash.cpanel.site
MAIL_PORT=465
MAIL_USERNAME=noreply@vehiclewash.cpanel.site
MAIL_PASSWORD=********
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@vehiclewash.cpanel.site
MAIL_FROM_NAME="Vehicle Wash Manager"
```

Test:

```bash
php artisan wash:daily-summary
```

---

## Checklist before go-live

- [ ] Document root → `public`
- [ ] `.env` filled (DB, APP_URL, APP_KEY, MAIL)
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Migrations run
- [ ] Cron `schedule:run` every minute
- [ ] HTTPS / SSL enabled in cPanel
- [ ] Demo admin password changed (or seed skipped)
- [ ] `storage` and `bootstrap/cache` writable (775)

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| 500 error | Check `storage/logs/laravel.log`; ensure `.env` + `APP_KEY` |
| Blank page | Document root must be `public`, not project root |
| CSS/JS missing | Filament assets: `php artisan filament:assets` |
| Permission denied | `chmod -R ug+rwx storage bootstrap/cache` |
| Cron not running | Use full path to `php` from MultiPHP (`which php` via SSH) |
| Composer not found | Use cPanel “Setup Node.js/PHP” or full path to composer |
