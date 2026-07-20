# Dummy Data Plan

> No real client data available yet. Phase 1 development and demos use Laravel seeders with realistic sample data. Real data can be imported later via Excel or admin panel.

---

## Seed Data Overview

| Entity | Count | Notes |
|--------|-------|-------|
| Organization | 1 | "Premium Car Wash Co." |
| Sites (malls) | 4 | Different cities, different contract values |
| Contracts | 4 | One per site, varying yearly amounts |
| Users | 12 | Owner, accountant, 4 managers, 2 partners, 5 staff (demo subset) |
| Staff | 20 | Washers + supervisors across sites (subset of ~50) |
| Service types | 4 | One wash package per site, different prices |
| Daily logs | 30 days | Last 30 days, morning + evening shifts |
| Wash entries | ~800 | ~50 cars/site/day across staff |

Phase 1 uses a **smaller staff subset (20)** for demo. Full 50-staff seed can be added later with factories.

---

## Sites (dummy)

| # | Mall Name | City | Yearly Contract | Wash Price | Manager |
|---|-----------|------|-----------------|------------|---------|
| 1 | City Center Mall | Dhaka | ৳600,000 | ৳200 | Karim Ahmed |
| 2 | Bashundhara Shopping Complex | Dhaka | ৳480,000 | ৳180 | Rahim Uddin |
| 3 | Jamuna Future Park | Dhaka | ৳720,000 | ৳220 | Hasan Ali |
| 4 | Chittagong GEC Circle Mall | Chittagong | ৳360,000 | ৳150 | Faruk Chowdhury |

---

## Users & Roles (dummy)

| Name | Email | Role | Password |
|------|-------|------|----------|
| Admin Owner | admin@carwash.test | Admin | password |
| Accountant | accountant@carwash.test | Accountant | password |
| Karim Ahmed | karim@carwash.test | Site Manager | password |
| Rahim Uddin | rahim@carwash.test | Site Manager | password |
| Hasan Ali | hasan@carwash.test | Site Manager | password |
| Faruk Chowdhury | faruk@carwash.test | Site Manager | password |
| Partner One | partner1@carwash.test | Partner | password |
| Partner Two | partner2@carwash.test | Partner | password |
| Staff 1–5 | staff1@carwash.test … | Staff | password |

> Demo passwords: `password` (change in production)

---

## Staff (dummy sample — 20 total)

Distributed across 4 sites, ~5 per site:

| Site | Staff names | Roles |
|------|-------------|-------|
| City Center Mall | Jamal, Selim, Babul, Anwar, Tarek | 4 washers, 1 supervisor |
| Bashundhara | Mokbul, Nurul, Shahid, Kamal, Iqbal | 4 washers, 1 supervisor |
| Jamuna Future Park | Rashid, Sohel, Mintu, Alamgir, Babu | 4 washers, 1 supervisor |
| Chittagong GEC | Nazmul, Rubel, Shakil, Imran, Dipu | 4 washers, 1 supervisor |

### Pay types (mixed — for Phase 3, stored in Phase 1)
- 60% daily wage (৳500/day)
- 25% monthly salary (৳12,000/month)
- 15% per-car (৳15/car)
- All: `has_housing = true`, `daily_food_allowance = ৳100`

---

## Partners (dummy)

| Partner | Site | Share % |
|---------|------|---------|
| Partner One (Abdul) | City Center Mall | 30% |
| Partner One (Abdul) | Bashundhara | 25% |
| Partner Two (Mohsin) | Jamuna Future Park | 40% |
| Partner Two (Mohsin) | Chittagong GEC | 35% |

---

## Daily Logs (dummy — last 30 days)

Generated per site, per day, per shift:

```
For each day (last 30 days):
  For each site (4):
    Morning shift:
      - 3–5 staff entries
      - 20–30 cars total
      - Payment mix: 70% cash, 20% UPI, 10% card
    Evening shift:
      - 3–5 staff entries
      - 15–25 cars total
      - Same payment mix
```

**Weekend boost:** Saturday/Sunday +20% car count (realistic mall footfall).

---

## Contracts (dummy)

| Site | Start | End | Annual Value | Status |
|------|-------|-----|--------------|--------|
| City Center | 2025-01-01 | 2025-12-31 | ৳600,000 | Active |
| Bashundhara | 2025-03-01 | 2026-02-28 | ৳480,000 | Active |
| Jamuna | 2024-06-01 | 2025-05-31 | ৳720,000 | Pending renewal |
| Chittagong GEC | 2025-01-01 | 2025-12-31 | ৳360,000 | Active |

> Jamuna contract expiring soon — useful for testing renewal alerts in Phase 2.

---

## Laravel Seeder Structure (when building)

```
database/seeders/
├── DatabaseSeeder.php
├── OrganizationSeeder.php
├── SiteSeeder.php
├── ContractSeeder.php
├── UserSeeder.php
├── StaffSeeder.php
├── PartnerSeeder.php
├── ServiceTypeSeeder.php
└── DailyLogSeeder.php      # uses factories for 30 days of data
```

Run: `php artisan migrate:fresh --seed`

---

## Replacing Dummy Data Later

When real client data arrives:

1. **Sites & contracts** — edit in Filament admin or bulk import CSV
2. **Staff** — CSV import (name, role, site, pay type)
3. **Historical wash logs** — Excel import matching their sheet format
4. **Clear dummy data** — `php artisan migrate:fresh` then import real data, OR delete org and re-import

Dummy data does not block production — seeders are dev/demo only.

---

## Currency & Locale

- Currency: **BDT (৳)** — Bangladesh Taka (Bangla-speaking client)
- Date format: `DD/MM/YYYY`
- Primary demo language: English + Bangla labels on key screens
