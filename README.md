# Vehicle Wash Manager

Multi-site vehicle wash business management system built with **Laravel 11 + Filament v3**.

## Features (Phase 1)

- Mall site & contract management
- Staff registry with pay types and site assignments
- Daily wash logging (morning/evening shifts)
- Mobile-friendly **Quick Daily Log** page for managers
- Owner dashboard: cars today, revenue by site, staff productivity
- Dummy seed data for demo (4 sites, 20 staff, 30 days of logs)
- English + Bangla UI strings

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

## Documentation

| Document | Description |
|----------|-------------|
| [PLAN.md](docs/PLAN.md) | Full system plan |
| [BUSINESS_REQUIREMENTS.md](docs/BUSINESS_REQUIREMENTS.md) | Business requirements |
| [TECH_STACK.md](docs/TECH_STACK.md) | Laravel + Filament stack |
| [SYSTEM_DESIGN.md](docs/SYSTEM_DESIGN.md) | Architecture |
| [CLIENT_DISCOVERY.md](docs/CLIENT_DISCOVERY.md) | Client answers |
| [DUMMY_DATA.md](docs/DUMMY_DATA.md) | Seed data reference |

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

## Next Phases

- **Phase 2:** Expenses, P&L, cash reconciliation
- **Phase 3:** Partner payouts, payroll, Arabic/Hindi/Urdu
