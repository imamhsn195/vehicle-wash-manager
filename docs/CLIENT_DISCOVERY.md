# Client Discovery — Answers & Gaps

> Captured from client discussion. Updated as more info comes in.

---

## 1. Sites & Contracts

| Question | Answer |
|----------|--------|
| Number of locations | More than 1 (exact count TBD) |
| Per-site details needed | Mall name, address, contract start/end, yearly amount |
| Mall fee type | **Fixed yearly rent** (not revenue share) |
| Contract structure | **Different per mall** |
| Site manager | **Any user** can be assigned as manager per site |

### Implications for system
- `Site` model with flexible manager assignment (user_id on site or pivot)
- `Contract` model per site with different amounts and dates
- Contract renewal alerts per site (different end dates)
- No revenue-share calculation with mall — only fixed cost allocation

---

## 2. Daily Operations

| Question | Answer |
|----------|--------|
| Current recording method | **Excel** |
| Data captured | **Car count per staff** (not per individual wash detail) |
| Service type breakdown | **No** — single wash type |
| Payment method | Yes, tracked (cash/card/UPI — any) |
| Shifts | **Both** morning and evening |
| Cars per site per day | ~**50** |

### Implications for system
- Daily log structure: `date + site + shift + staff + car_count + payment_method`
- No service type selector needed in Phase 1 (one package)
- Shift field required: `morning` | `evening`
- Excel import feature valuable for migration
- Manager Livewire page: simple — pick staff, enter count, pick payment method, pick shift

### Simplified daily log form (Phase 1)
```
Site:     [auto — manager's site]
Date:     [auto — today]
Shift:    [Morning / Evening]
Staff:    [dropdown]
Cars:     [number]
Payment:  [Cash / Card / UPI / Other]
[+ Add another staff entry]
[Submit]
```

---

## 3. Staff

| Question | Answer |
|----------|--------|
| Staff list | Name, role, assigned site — **full list TBD** |
| Payment structure | **Mixed** — daily wage, monthly salary, and/or per-car |
| Company housing | **All staff** live in company-provided housing |
| Food | **Daily cost per staff** (not per site) |

### Implications for system
- `Staff` model needs `salary_type` enum: `daily | monthly | per_car | hybrid`
- `has_housing = true` for all (can still be a flag for reporting)
- `daily_food_allowance` field per staff (amount)
- Housing cost likely tracked as **organization-wide fixed cost** allocated across sites
- Food cost = `daily_food_allowance × staff_count × days` — auto-calculable
- Payroll logic more complex due to mixed pay types — **Phase 3**, but schema ready in Phase 1

---

## 4. Wash Services & Pricing

| Question | Answer |
|----------|--------|
| Packages offered | **One package only** |
| Price same across sites | **TBD** |
| Seasonal / weekend pricing | **TBD** |

### Implications for system
- Single `ServiceType` per site (or one global) — keeps UI very simple
- Price may still differ per site even with one package type
- No seasonal pricing logic needed in Phase 1
- Revenue = `car_count × site_price` (simple multiplication)

---

## 5. Partners

| Question | Answer |
|----------|--------|
| Number of partners | **TBD** |
| Revenue share | **Different % per site** |
| Payout frequency | **Monthly** |
| Partner access | **Own login** required |

### Implications for system
- `PartnerSiteShare` pivot: `partner_id + site_id + share_pct`
- Partner Filament panel (read-only or limited) — **Phase 3**, but user role ready in Phase 1
- Monthly settlement report and payout tracking
- Partner sees only their assigned sites' data

---

## 6. Costs (Phase 2 — Finance)

| Question | Answer |
|----------|--------|
| Cost tracking today | **Not sure** if in Excel/spreadsheet |
| Historical data (3–6 months) | **Not sure** if available |

### Cost categories to support (when they start tracking)

| Type | Categories |
|------|------------|
| **Fixed** | Mall contract, equipment purchase, staff housing rent, office rent |
| **Variable** | Chemicals, water, towels, staff food, fuel, repairs |

### Implications for system
- Expense module must be **easy to adopt from scratch** — they may not have clean historical data
- Pre-configure default cost categories in Filament (admin can add more)
- Phase 2 should not depend on Excel import for expenses — manual entry first
- Staff food can be **auto-calculated** from daily allowance × staff count (already known from Phase 1)
- Housing rent entered once as monthly fixed cost, allocated across sites
- Mall contract cost auto-pulled from `Contract` model (no double entry)

---

## 7. Equipment

| Question | Answer |
|----------|--------|
| Equipment list | **They will enter in system or share a file** |
| Purchase cost & date | Yes — for depreciation |
| Breakdown frequency | **TBD** |

### Implications for system
- `Equipment` resource in Filament — admin/manager can add machines per site
- Fields: name, site, purchase date, purchase cost, warranty end, status
- Optional Excel import template for bulk equipment entry
- Maintenance log per equipment (repair cost, downtime days) — Phase 2
- Depreciation: simple straight-line (purchase cost ÷ useful life years) for P&L
- Breakdown frequency not blocking — track via maintenance records over time

---

## 8. Payment Flow

| Question | Answer |
|----------|--------|
| Who collects payment | **Staff and manager** (direct from customers) |
| Mall takes a cut? | **No** |
| Mall bills on their behalf? | **No** |
| Revenue deposit frequency | **Daily** |

### Implications for system
- All revenue stays with the business — no mall commission to deduct
- Payment method on daily log is for **tracking only** (cash vs card vs UPI split)
- No mall billing integration needed
- Optional Phase 2 feature: **daily cash reconciliation**
  - Expected revenue (from wash logs) vs actual cash deposited
  - Flag discrepancies per site per day
- Manager likely hands over cash to owner/admin daily — system can track "deposited" status on daily log

### Suggested daily log addition (Phase 2)
```
End of day:
  Total revenue (auto-calculated):  ₹X,XXX
  Cash collected:                   ₹X,XXX
  Deposited:                        [Yes / No]
  Deposited amount:                 ₹X,XXX
```

---

## Refined Phase 2 Scope (Finance)

Based on answers above:

### Build
- [x] Expense entry with pre-set categories (fixed / variable)
- [x] Mall contract cost auto-linked from site contracts
- [x] Staff food cost auto-calculated (daily allowance × active staff)
- [ ] Housing rent as monthly fixed cost (org-wide, allocated to sites)
- [x] Equipment registry (manual entry + optional file import)
- [ ] Equipment maintenance log
- [x] Monthly P&L per site
- [x] Cost per wash calculation
- [x] Daily cash reconciliation (revenue vs deposited)
- [x] Expense approval workflow (manager submits → admin approves)

### Not needed (confirmed)
- Mall revenue share / commission calculation
- Mall billing integration
- Historical expense Excel import (unless they find data later)

### Defer to Phase 3
- [x] Partner monthly payout reports
- [x] Mixed payroll calculation
- [ ] Equipment depreciation in formal reports

---

## Refined Phase 1 MVP Scope

Based on answers above, Phase 1 should include:

### Build
- [ ] Laravel + Filament setup
- [ ] Roles: Admin, Site Manager, Partner, Accountant, Staff
- [ ] Multi-language: English + Bangla (language switcher)
- [ ] Sites CRUD with contract details and manager assignment
- [ ] Staff CRUD with site assignment, salary type, food allowance
- [ ] Bulk staff/user import (CSV)
- [ ] Single wash price per site
- [ ] Daily log: shift + staff + car count + payment method
- [ ] Manager Livewire mobile page (simplified form above)
- [ ] Excel import for historical daily logs
- [ ] Owner dashboard: daily revenue per site, cars today, staff productivity
- [ ] Shift filter on reports (morning / evening / both)

### Defer to Phase 2
- Expense tracking and P&L
- Housing cost allocation
- Food cost auto-calculation in reports
- Monthly profit per site report
- Contract renewal alerts
- Arabic language

### Defer to Phase 3
- [x] Partner portal and monthly payout reports
- [x] Mixed payroll calculation
- [ ] Hindi + Urdu languages

---

## Data Strategy

**No real client data available yet.** Development uses **dummy seed data** — see [DUMMY_DATA.md](DUMMY_DATA.md).

- 4 mall sites, 20 staff, 12 users, 30 days of wash logs
- Real client data can replace via admin panel or Excel import later
- Phase 1 is **unblocked** — ready to build

---

## Still Needed from Client (when available)

Real data can replace dummy seeders at any time:

| # | Item | Status |
|---|------|--------|
| 1 | Site list | Using dummy (4 sites) |
| 2 | Staff list | Using dummy (20 staff) |
| 3 | Wash prices | Using dummy (৳150–220) |
| 4 | Sample Excel | Using generated format |
| 5 | Staff logins in Phase 1? | Default: managers only first |

### Phase 2+ (when available)
| # | Item |
|---|------|
| 6 | Daily food cost per staff |
| 7 | Monthly housing rent |
| 8 | Partners + share % per site |
| 9 | Budget / deadline |

---

## Key Simplifications (good news)

These answers make Phase 1 **simpler and faster**:

1. **One wash package** — no service type selector
2. **Count per staff** — not per-car transaction logging
3. **Fixed mall rent** — no complex revenue-share with malls
4. **Excel today** — import path is clear for migration

## Key Complexities (plan ahead)

1. **Mixed pay types** — payroll logic needs careful design (Phase 3)
2. **All staff housed + daily food** — cost allocation across sites (Phase 2)
3. **Different partner % per site** — pivot table + monthly settlement (Phase 3)
4. **Two shifts** — daily log unique per site + date + shift (not just date)
5. **No historical finance data** — expense module must be easy to start fresh (Phase 2)
6. **Daily cash handling** — staff/manager collect cash, deposited daily; reconciliation useful (Phase 2)
7. **Multi-language UI** — English, Arabic, Hindi, Urdu, Bangla (Phase 1 English + Bangla, others phased)
8. **All users need login** — ~50+ staff accounts; role-based access critical from day one

---

## 9. Users & Access

| Question | Answer |
|----------|--------|
| Who needs login besides owner? | **Everyone** — all roles |
| Site managers | **TBD** (count) — all get login |
| Accountant | **Yes** (included in "all") |
| Partners | **Yes** (included in "all") |
| Staff (washers) | **Yes** (included in "all") |
| UI language | **English, Arabic, Hindi, Urdu, and Bangla** |
| Manager phones | **TBD** (personal vs company) |

### Implications for system
- **Every user gets an account** — owner, managers, partners, accountant, and staff
- Role-based Filament panels:
  - **Admin** — full access
  - **Site Manager** — daily log + own site data
  - **Partner** — read-only, assigned sites only
  - **Accountant** — finance modules
  - **Staff** — view own stats, optional self-logging (limited panel or simple page)
- With ~50 staff, bulk user import needed (CSV/Excel)
- Staff may only need a **minimal view** (own cars washed, earnings) — not full admin panel
- **Multi-language (i18n)** required:
  - Phase 1: **English + Bangla** (primary)
  - Phase 2+: Arabic, Hindi, Urdu
  - Laravel localization + Filament translation files
  - Language switcher in header
- Mobile-friendly critical if managers use personal phones (likely)

---

## 10. Reports (Priority)

Owner wants **all** of these:

| Report | Phase | Description |
|--------|-------|-------------|
| **Daily revenue per site** | 1 | Cars × price, per site, today |
| **Monthly profit per site** | 2 | Revenue − costs, per site, per month |
| **Staff productivity** | 1 | Cars washed per staff per day/week |
| **Partner payout statement** | 3 | Monthly profit share per partner per site |
| **Contract renewal reminder** | 2 | Alert when mall contract expires in 60/30/7 days |

### Implications for system
- Phase 1 dashboard must include: daily revenue per site + staff productivity
- Phase 2 adds: monthly P&L + contract renewal alerts
- Phase 3 adds: partner payout statement (PDF export)
- All reports should be exportable to **Excel and PDF**

---

## 11. Existing Tools

| Question | Answer |
|----------|--------|
| Current software | Excel (daily logs) — no other accounting software confirmed |
| Replace or integrate? | **No integration** — standalone system |

### Implications for system
- No API integrations needed (accounting software, WhatsApp, etc.)
- Excel import for migration only — system replaces Excel going forward
- Optional future: WhatsApp daily summary to owner (Phase 5)

---

## 12. Budget & Timeline

| Question | Answer |
|----------|--------|
| Budget range | **TBD** |
| Hard deadline | **TBD** |
| Who maintains after launch? | **You (the developer)** |

### Implications for system
- Code must be **maintainable** — clean Laravel conventions, documented
- Filament chosen partly for long-term maintainability
- You will handle updates, bug fixes, and new features
- No deadline pressure confirmed — phased delivery is fine
- Document deployment and backup procedures for handoff to ongoing maintenance

---

## Priority Reports by Phase

```
Phase 1 Dashboard:
  ├── Daily revenue per site
  ├── Cars washed today (total + per site)
  └── Staff productivity (cars per person)

Phase 2 Reports:
  ├── Monthly profit per site (P&L)
  ├── Cost per wash
  └── Contract renewal reminders

Phase 3 Reports:
  ├── Partner payout statement (monthly)
  └── Payroll summary
```

---

## Language Rollout Plan

| Phase | Languages |
|-------|-----------|
| Phase 1 | English + **Bangla** |
| Phase 2 | + Arabic |
| Phase 3 | + Hindi, Urdu |

Technical approach: Laravel `lang/` files + Filament translations, user-level language preference in profile.

---

## User Accounts Plan

| Role | Count (est.) | Panel access |
|------|--------------|--------------|
| Owner/Admin | 1–2 | Full Filament admin |
| Site Managers | ~5–10? | Livewire daily log + site view |
| Accountant | 1 | Finance resources + reports |
| Partners | TBD | Read-only partner panel |
| Staff | ~50 | Minimal: own stats only |

**Note:** 50 staff logins is a lot — consider whether all staff need login in Phase 1, or only managers + staff self-view in Phase 2. Confirm with client.
