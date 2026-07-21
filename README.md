# Vehicle Wash Manager

Multi-site vehicle wash business management system built with **Laravel 11 + Filament v3**.

## Features (Phase 1–5)

- Mall site & contract management
- Staff registry with pay types and site assignments
- Daily wash logging (morning/evening shifts)
- Mobile-friendly **Quick Daily Log** page for managers
- Owner dashboard: cars today, revenue by site, staff productivity
- **Expenses** with approval workflow (pending → approved/rejected)
- **Cash reconciliation** (expected vs collected vs deposited)
- **Equipment** registry per site
- **Monthly Site P&L** report (revenue, expenses, profit, margin, cost/wash)
- **Partners** with per-site share % and monthly payout settlements
- **Payroll** for daily / monthly / per-car / hybrid pay types
- **Break-even analysis** — cars/day needed per site
- **Alerts** — contract renewals + missing daily logs
- **Excel exports** — daily logs, expenses, site P&L
- **My Stats** — staff self-view (cars + estimated earnings)
- **Daily summary email** — `php artisan wash:daily-summary` (scheduled 21:00)
- **Languages** — English, Bangla, Arabic, Hindi, Urdu
- Dummy seed data for demo
- **TDD** — see [docs/TDD.md](docs/TDD.md)

## Requirements

- PHP 8.2+
- Composer
- SQLite (default) or PostgreSQL

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Open **http://localhost:8000/admin**

## Demo Logins

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@carwash.test | password |
| Site Manager | karim@carwash.test | password |
| Accountant | accountant@carwash.test | password |
| Partner | partner1@carwash.test | password |
| Staff | staff1@carwash.test | password |

## Scheduled jobs

```bash
php artisan wash:daily-summary   # email admins today's summary
php artisan schedule:work        # renewals 08:00, summary 21:00
```

## Documentation

| Document | Description |
|----------|-------------|
| [PLAN.md](docs/PLAN.md) | Full system plan |
| [BUSINESS_REQUIREMENTS.md](docs/BUSINESS_REQUIREMENTS.md) | Business requirements |
| [TECH_STACK.md](docs/TECH_STACK.md) | Laravel + Filament stack |
| [SYSTEM_DESIGN.md](docs/SYSTEM_DESIGN.md) | Architecture |
| [CLIENT_DISCOVERY.md](docs/CLIENT_DISCOVERY.md) | Client answers |
| [DUMMY_DATA.md](docs/DUMMY_DATA.md) | Seed data reference |
| [TDD.md](docs/TDD.md) | Test-driven development workflow |

## Running tests

```bash
php artisan test
```

See [docs/TDD.md](docs/TDD.md) for the test-driven development workflow.

## Tech Stack

- Laravel 11
- Filament v3
- SQLite / PostgreSQL
- Livewire (Quick Daily Log page)

## Next / optional

- WhatsApp daily summaries (API integration)
- Housing rent allocation across sites
- Equipment maintenance log
- PDF partner statements

## cPanel deploy (vehiclewash.cpanel.site)

See **[deploy/cpanel/README.md](deploy/cpanel/README.md)** for full checklist.

```bash
# First time
git clone https://github.com/imamhsn195/vehicle-wash-manager.git
cd vehicle-wash-manager
chmod +x deploy/cpanel/*.sh
./deploy/cpanel/setup-first.sh   # creates .env — edit DB/URL, re-run

# Point domain document root → .../vehicle-wash-manager/public
# Add cron from deploy/cpanel/cron.txt

# Every update
./deploy/cpanel/pull-and-deploy.sh
```