# Vehicle Wash Manager — System Design

> Stack: **Laravel 11 + Filament v3 + PostgreSQL**

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENTS                               │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │ Admin    │  │ Manager  │  │ Partner  │  │Accountant│   │
│  │ Filament │  │ Livewire │  │ Filament │  │ Filament │   │
│  │ Panel    │  │ Mobile   │  │ (read)   │  │ (finance)│   │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘   │
└───────┼─────────────┼─────────────┼─────────────┼───────────┘
        │             │             │             │
        └─────────────┴──────┬──────┴─────────────┘
                             │
                    ┌────────▼────────┐
                    │  Laravel 11 App │
                    │  Filament v3    │
                    │  Livewire       │
                    │  Eloquent ORM   │
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
     ┌────────▼───┐  ┌──────▼──────┐  ┌───▼────┐
     │ PostgreSQL │  │File Storage │  │ Queues │
     │  Database  │  │ (S3/local)  │  │(alerts)│
     └────────────┘  └─────────────┘  └────────┘
```

---

## Tech Stack

| Layer | Technology | Why |
|-------|-----------|-----|
| Framework | Laravel 11 | Business logic, auth, queues, mail |
| Admin UI | Filament v3 | CRUD, tables, forms, widgets, exports |
| Manager mobile | Custom Livewire page | Phone-friendly daily wash log |
| Styling | Tailwind CSS (via Filament) | Consistent admin UI |
| Charts | Filament ApexCharts | Dashboards and P&L trends |
| Database | PostgreSQL | Relational data, financial accuracy |
| ORM | Eloquent | Models, relationships, scopes |
| Auth | Laravel Auth + Filament Shield | Role-based panel access |
| Permissions | Laravel Policies | Site-scoped data for managers |
| Excel | Laravel Excel | Export wash logs, P&L |
| PDF | Laravel DomPDF | Partner statements, reports |
| Deployment | Laravel Forge + VPS | Simple, affordable hosting |

---

## Entity Relationship (Core Model)

```
Organization
  ├── Sites (mall parkings)
  │     ├── Contracts (yearly mall agreements)
  │     ├── ServiceTypes (wash packages + pricing)
  │     ├── DailyLogs (date, shift, manager)
  │     │     └── WashEntries (staff, service, count, payment)
  │     ├── Expenses (fixed/variable, category, amount)
  │     └── Equipment (assets, maintenance)
  │
  ├── Staff (washers, managers)
  │     ├── SiteAssignments
  │     ├── Attendance
  │     └── PayrollRecords
  │
  ├── Partners
  │     ├── PartnerSiteShares (% per site)
  │     └── PartnerSettlements
  │
  └── Users (linked to roles)
```

---

## Filament Module Mapping

### Operations
| Feature | Implementation |
|---------|----------------|
| Create daily log | `DailyLogResource` or Livewire `DailyLogEntry` page |
| Add wash entries | Repeater field or `WashEntry` relation manager |
| View log by date | Table filter on `DailyLogResource` |
| Close day | `is_closed` toggle on daily log |

### Analytics (custom Filament pages)
| Feature | Implementation |
|---------|----------------|
| Owner dashboard KPIs | Filament dashboard widgets |
| Site P&L | `SitePnLReport` page — Eloquent aggregation |
| Site ranking | `SiteRankingWidget` — margin by site |
| Break-even | `BreakEvenAnalysis` page — calculated per site |

### Finance
| Feature | Implementation |
|---------|----------------|
| Record expense | `ExpenseResource` with approval status |
| Approve expense | Table action (admin/accountant) |
| Monthly P&L | `SitePnLReport` with date range filter |
| Export | Laravel Excel export action on resources |

---

## Key Calculations (Laravel Services)

Business logic lives in dedicated service classes (e.g. `App\Services\AnalyticsService`):

### Revenue (per site, per day)
```
revenue = Σ (wash_count × service_price) for each entry
```

### Cost Per Wash
```
cost_per_wash = (variable_costs + allocated_fixed_costs) / total_cars_washed
```

### Site Profit
```
profit = revenue - variable_costs - fixed_costs_allocated
margin = profit / revenue × 100
```

### Break-Even (cars per day)
```
break_even_cars = daily_fixed_cost / (avg_price_per_wash - variable_cost_per_wash)
```

### Partner Payout
```
partner_payout = site_profit × partner_share_percentage
```

---

## Security

| Concern | Approach |
|---------|----------|
| Authentication | Laravel session auth via Filament login |
| Authorization | Filament Shield roles + Laravel Policies |
| Site scoping | Eloquent global scopes — managers see only assigned sites |
| Partner access | Policy limits to partner's sites only |
| Financial audit | Model observers or `activity log` package for expense/log edits |
| File uploads | Validated mime types, stored outside public web root |
| HTTPS | Enforced via Forge / Nginx |

---

## Mobile Considerations (Manager Daily Log)

Filament admin is **not** ideal on phones for field use. Build a dedicated **Livewire page**:

- Auto-fill date and site (from logged-in manager)
- Large dropdowns: staff, service type
- Number input for car count
- Payment method toggle (cash / card / UPI)
- "Add another entry" button for multiple staff
- Submit and show today's running total
- Minimal navigation — one screen, one job

Optional Phase 2: PWA manifest for "Add to Home Screen" on manager phones.

---

## Queue Jobs & Notifications

| Job | Trigger | Action |
|-----|---------|--------|
| `ContractRenewalReminder` | Daily schedule | Email owner when contract expires in 60/30/7 days |
| `MissingDailyLogAlert` | End of day | Notify owner of sites with no log submitted |
| `DailySummaryEmail` | End of day | Cars washed + revenue summary to owner |
| `GenerateMonthlyPnL` | 1st of month | Pre-compute P&L cache for dashboards |

---

## Folder Structure (planned)

```
app/
├── Filament/
│   ├── Resources/          # SiteResource, StaffResource, etc.
│   ├── Pages/              # SitePnLReport, BreakEvenAnalysis
│   └── Widgets/            # CarsTodayWidget, RevenueWidget
├── Livewire/
│   └── DailyLogEntry.php   # Manager mobile log
├── Models/                 # Site, Staff, DailyLog, Expense, etc.
├── Policies/               # SitePolicy, ExpensePolicy
└── Services/
    ├── AnalyticsService.php
    ├── PnLService.php
    └── PartnerPayoutService.php
```

---

## Deployment

1. Provision VPS (DigitalOcean / Hetzner) via Laravel Forge
2. PHP 8.3, PostgreSQL, Nginx, Redis (for queues)
3. `php artisan migrate --force` on deploy
4. Forge zero-downtime deploy on git push
5. Scheduled tasks via Forge cron: `php artisan schedule:run`
