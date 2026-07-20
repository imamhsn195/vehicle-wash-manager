# Vehicle Wash Business — System Plan

> Planning document only. No implementation started.

---

## 1. Business Understanding

Your client runs a **multi-location vehicle wash operation** inside mall parking areas.

| Aspect | Details |
|--------|---------|
| Scale | ~50 staff + site managers across multiple mall locations |
| Daily ops | Staff wash cars; managers record how many cars washed per day |
| Stakeholders | Owner, partners (with revenue share), site managers, washers |
| Revenue | Per-car wash fees (may vary by service type and location) |
| Fixed costs | Yearly mall parking contracts, equipment, staff housing, office rent |
| Variable costs | Consumables per wash, staff food, utilities, maintenance |

**Goal:** A single system to run daily operations, track all money in/out, and give the owner data to make profitable decisions.

---

## 2. Who Uses the System

| Role | What they need |
|------|----------------|
| **Owner / Admin** | Full view: all sites, P&L, partners, contracts, alerts |
| **Partner** | Profit and performance for their assigned sites only |
| **Site Manager** | Quick daily logging on phone: cars washed, staff, expenses |
| **Staff (Washer)** | Optional: view schedule, own productivity (Phase 2+) |
| **Accountant** | Expenses, payroll, invoices, financial reports |

---

## 3. System Modules

### Module A — Sites & Contracts
- Register each mall parking location
- Store yearly contract: value, dates, renewal date, terms
- Assign manager and staff to each site
- Define wash packages and prices per site

### Module B — Daily Operations (highest priority)
- Manager logs each day: date, shift, cars washed, by staff and service type
- Payment split: cash / card / UPI / mall billing
- End-of-day summary per site
- Must work well on mobile (managers are in parking lots)

### Module C — Staff Management
- Staff profiles: role, site, salary type (daily / monthly / per-wash)
- Attendance and shift tracking
- Housing and food allowance flags
- Productivity: cars per staff per day

### Module D — Financial Tracking
**Revenue** — auto-calculated from daily wash logs

**Fixed costs**
- Mall contract fees (yearly → monthly allocation)
- Equipment purchase and depreciation
- Staff housing rent
- Office rent, insurance

**Variable costs**
- Consumables per wash (water, chemicals, towels)
- Staff food per site/day
- Equipment repair, fuel, electricity

**Expense workflow:** Manager submits → Admin approves

### Module E — Partners
- Partner profile with revenue share % (per site or global)
- Auto-calculate profit share per period
- Settlement and payout history
- Partner login to view their sites only

### Module F — Equipment
- Asset list per site: machines, purchase cost, warranty
- Maintenance log and repair costs
- Downtime tracking (lost revenue when machine is down)

### Module G — Analytics & Decisions
Dashboards the owner needs:

| Question | Report |
|----------|--------|
| How many cars today? | Live ops dashboard |
| Is Site X profitable? | Site P&L (revenue − costs) |
| What does each wash cost us? | Cost per wash |
| How many cars to break even? | Break-even calculator per site |
| Which site performs best? | Site ranking by margin |
| Should we renew a mall contract? | Contract ROI over the year |
| How much do we owe partners? | Partner payout report |
| Are managers submitting logs? | Missing daily log alerts |

**Alerts:** Contract renewal in 60 days, site below break-even, missing logs, unusual expenses

---

## 4. Key Calculations

```
Daily Revenue     = Σ (cars washed × service price)
Variable Costs    = consumables + food + per-wash expenses
Fixed Costs       = (monthly fixed costs) ÷ days in month
Site Profit       = Revenue − Variable − Fixed (allocated)
Profit Margin %   = Profit ÷ Revenue × 100
Cost per Wash     = Total Costs ÷ Total Cars
Break-even Cars   = Daily Fixed Cost ÷ (Avg Price − Variable Cost per Wash)
Partner Payout    = Site Profit × Partner Share %
```

---

## 5. Implementation Phases

### Phase 1 — Core Operations (start here)
- User login with roles (Admin, Manager)
- Site setup and staff registry
- Daily wash logging (mobile-friendly)
- Basic dashboard: today's cars and revenue

### Phase 2 — Finance
- Expense entry and approval
- Monthly P&L per site
- Cost per wash and margin reports

### Phase 3 — Partners & Payroll
- Partner shares and payout reports
- Staff payroll (daily/monthly/per-wash)
- Housing and food cost allocation

### Phase 4 — Intelligence
- Break-even analysis
- Site comparison and trends
- Contract renewal reminders
- PDF/Excel export

### Phase 5 — Mobile & Automation (later)
- Staff self-logging app
- WhatsApp daily summary to owner
- Payment gateway integration

---

## 6. Information to Collect from Client First

Before building anything, get:

1. List of all mall sites with contract details
2. Full staff list: names, sites, pay structure
3. Partner names and share percentages
4. Wash service types and prices at each location
5. Historical data (even Excel): 3–6 months of wash counts and expenses
6. All cost categories they track today
7. Equipment list with purchase costs
8. Preferred language for the UI
9. How customers pay (cash-heavy? mall billing?)

---

## 7. Technology Stack (chosen)

| Layer | Technology | Why |
|-------|------------|-----|
| Framework | **Laravel 11** | Business logic, auth, reports, queues |
| Admin panel | **Filament v3** | Fast CRUD for sites, staff, expenses, partners |
| Manager mobile log | **Custom Livewire page** | Simple, phone-friendly daily wash entry |
| Database | **PostgreSQL** | Accurate financial data, relationships |
| Auth & roles | **Filament Shield** + Laravel policies | Owner, manager, partner, accountant access |
| Charts | **Filament ApexCharts** | Revenue, profit, site comparison dashboards |
| Excel export | **Laravel Excel** | P&L and wash log exports |
| PDF reports | **Laravel DomPDF** | Partner statements, monthly reports |
| File storage | **Laravel Storage (S3)** | Contract PDFs, expense receipts |
| Hosting | **Laravel Forge + VPS** (DigitalOcean / Hetzner) | Low maintenance, affordable |
| Queues / alerts | **Laravel Queues** | Contract renewal reminders, daily summaries |

### Why Laravel + Filament

- **Admin-heavy app** — most work is sites, staff, logs, expenses, partners; Filament handles this quickly
- **Lower build cost** — less custom UI code than a React/Next.js admin from scratch
- **Strong financial logic** — Eloquent models fit contracts, expenses, payroll, partner shares
- **Built-in exports** — Excel/PDF for accountant and owner
- **Mobile managers** — one custom Livewire page for daily logging (not the full Filament admin on phone)

See `docs/TECH_STACK.md` and `docs/SYSTEM_DESIGN.md` for full technical details.

---

## 8. Success Criteria

| Metric | Target |
|--------|--------|
| Daily log completion | 100% of sites logged by end of day |
| Manager data entry time | Under 5 minutes per site |
| Monthly P&L ready | By 2nd of each month |
| Owner decision speed | "Is Site X profitable?" answered in under 30 seconds |
| Revenue accuracy | Within 2% of actual cash collected |

---

## 9. Client Discovery (confirmed)

See [CLIENT_DISCOVERY.md](CLIENT_DISCOVERY.md) for full details. Key confirmed facts:

| Area | Confirmed |
|------|-----------|
| Sites | Multiple malls, different fixed yearly contracts per site |
| Daily ops | Excel today → car count per staff, morning + evening shifts, ~50 cars/site/day |
| Staff | Mixed pay (daily/monthly/per-car), all in company housing, daily food cost per staff |
| Services | **One wash package** only |
| Partners | Different share % per site, monthly payout, own login |
| Payment | Staff/manager collect directly — no mall cut, deposited daily |
| Finance data | No confirmed historical spreadsheets — build expense entry from scratch |
| Users | **Everyone** gets login — owner, managers, partners, accountant, staff |
| Languages | English, Arabic, Hindi, Urdu, Bangla (Phase 1: English + Bangla) |
| Reports | Daily revenue, monthly P&L, staff productivity, partner payouts, contract alerts |
| Integration | Standalone — no third-party software integration |
| Maintenance | Developer (you) maintains after launch |

### Phase 1 MVP (refined)
- Sites + contracts + manager assignment
- Staff registry with salary type and food allowance
- Daily log: shift + staff + car count + payment method (Livewire mobile page)
- Excel import for migration
- Owner dashboard: cars and revenue today
- **Dummy seed data** for demo (4 sites, 20 staff, 30 days logs) — see `DUMMY_DATA.md`

**Status: Ready to build** — no client data required to start.

---

## 10. Suggested Next Steps

1. **Collect remaining info** — see gaps in CLIENT_DISCOVERY.md (staff list, prices, sample Excel)
2. **Validate Phase 1 scope** with client
3. **Design wireframes** for manager daily log and owner dashboard
4. **Start building** Phase 1
