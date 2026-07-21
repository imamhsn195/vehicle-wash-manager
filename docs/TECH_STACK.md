# Technology Stack — Laravel + Filament

> Chosen stack for the Vehicle Wash Business Management System.

---

## Stack Summary

```
Laravel 11 + Filament v3 + PostgreSQL + Livewire
```

| Layer | Technology |
|-------|------------|
| Backend framework | Laravel 11 |
| Admin panel | Filament v3 |
| Manager mobile UI | Custom Livewire page |
| Database | PostgreSQL |
| ORM | Eloquent |
| Auth & permissions | Filament Shield + Laravel Policies |
| Charts | Filament ApexCharts plugin |
| Excel export | maatwebsite/excel (Laravel Excel) |
| PDF reports | barryvdh/laravel-dompdf |
| File uploads | Laravel Storage (local or S3) |
| Email alerts | Laravel Mail + Queue |
| Hosting | Laravel Forge + DigitalOcean / Hetzner VPS |

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         USERS                                │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │ Owner    │  │ Manager  │  │ Partner  │  │Accountant│   │
│  │ Admin    │  │ (mobile) │  │ Portal   │  │          │   │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘   │
└───────┼─────────────┼─────────────┼─────────────┼───────────┘
        │             │             │             │
        ▼             ▼             ▼             ▼
┌─────────────────────────────────────────────────────────────┐
│                   Laravel Application                        │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────┐ │
│  │ Filament Admin  │  │ Livewire Mobile │  │   Queues    │ │
│  │ Panel (v3)      │  │ Daily Log Page  │  │  (alerts)   │ │
│  └────────┬────────┘  └────────┬────────┘  └──────┬──────┘ │
│           └────────────────────┼──────────────────┘         │
│                                ▼                             │
│                    Eloquent Models + Services                │
└────────────────────────────────┬────────────────────────────┘
                                 │
                    ┌────────────┼────────────┐
                    ▼            ▼            ▼
              PostgreSQL    File Storage    Email/SMS
```

---

## Filament Resources (planned)

| Business entity | Filament resource | Notes |
|-----------------|-------------------|-------|
| Sites | `SiteResource` | Mall parking locations |
| Contracts | `ContractResource` | Linked to site, renewal dates |
| Service types | `ServiceTypeResource` | Wash packages + pricing |
| Staff | `StaffResource` | Site assignments via relation manager |
| Daily logs | `DailyLogResource` | Wash entries as repeater/relation |
| Expenses | `ExpenseResource` | Approval workflow (pending → approved) |
| Equipment | `EquipmentResource` | Maintenance as relation |
| Partners | `PartnerResource` | Site share % via relation |
| Payroll | `PayrollRecordResource` | Phase 3 |
| Users | `UserResource` | Admin only |

### Custom Filament pages

| Page | Purpose |
|------|---------|
| `Dashboard` | Owner KPIs: cars today, revenue, top sites |
| `SitePnLReport` | Monthly profit & loss per site |
| `BreakEvenAnalysis` | Cars/day needed per site |
| `PartnerPayoutReport` | Profit share statements |
| `MissingLogsReport` | Sites that haven't submitted today |

### Custom Livewire page (mobile)

| Page | Purpose |
|------|---------|
| `DailyLogEntry` | Manager quick log: staff, service, count, payment — large touch targets, minimal fields |

---

## User Access by Role

| Role | Access |
|------|--------|
| **Owner / Admin** | Full Filament panel |
| **Accountant** | Expenses, payroll, reports (scoped permissions) |
| **Site Manager** | Custom Livewire daily log + own site view only |
| **Partner** | Read-only Filament panel or custom dashboard (assigned sites) |
| **Staff** | Optional later — simple view of own stats |

Permissions managed via **Filament Shield** with Laravel policies scoping data by site.

---

## Key Packages

```bash
# Core
composer require filament/filament:"^3.0"

# Permissions
composer require bezhansalleh/filament-shield

# Charts
composer require leandrocfe/filament-apex-charts

# Excel export
composer require maatwebsite/excel

# PDF
composer require barryvdh/laravel-dompdf
```

---

## Hosting & DevOps

| Item | Choice |
|------|--------|
| Server | DigitalOcean or Hetzner VPS (2GB+ RAM) |
| Deploy | Laravel Forge (or manual with Nginx + PHP 8.3) |
| SSL | Let's Encrypt (via Forge) |
| Database | PostgreSQL on same VPS or managed (e.g. Supabase) |
| Backups | Forge daily backups + DB dumps |
| Domain | Client's own domain |
| Staging | Separate Forge site for testing |

**Estimated monthly cost:** $15–30 (VPS + domain)

---

## Why Not Next.js (for this project)

Next.js was considered but Laravel + Filament is a better fit because:

- This is an **admin/operations system**, not a consumer app
- **Faster to build** CRUD, filters, exports, and role-based panels
- **Lower dev cost** for the same feature set
- Team familiarity with PHP/Laravel (if applicable)
- Manager mobile need is met with **one custom Livewire page**, not a full separate frontend

Next.js would be preferred only if mobile-first UX or heavy custom UI were the top priority.

---

## Phase 1 Tech Deliverables

1. Laravel project setup + Filament install
2. PostgreSQL schema (migrations)
3. Filament Shield roles: Admin, Manager, Partner, Accountant, Staff
4. Multi-language: English + Bangla (`lang/en`, `lang/bn`)
5. Resources: Sites, Staff, ServiceTypes, DailyLogs
6. Bulk user/staff CSV import
7. Custom Livewire page: manager daily log
8. Dashboard widgets: daily revenue per site, staff productivity
9. Deploy to staging VPS

## Localization

| Language | Code | Phase |
|----------|------|-------|
| English | `en` | 1 |
| Bangla | `bn` | 1 |
| Arabic | `ar` | 2 |
| Hindi | `hi` | 3 |
| Urdu | `ur` | 3 |

Uses Laravel `lang/` files + Filament translations. User selects language in profile; RTL support required for Arabic and Urdu.
