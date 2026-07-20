# Vehicle Wash Business Management System — Business Requirements

## 1. Business Overview

Your client operates a **multi-site vehicle wash business** across mall parking locations. The business model combines:

| Dimension | Description |
|-----------|-------------|
| **Revenue** | Per-car wash fees (possibly tiered by service type) |
| **Fixed costs** | Yearly mall parking contracts, equipment/machinery, staff housing, office rent |
| **Variable costs** | Consumables per wash (water, chemicals, towels), food for staff |
| **People** | ~50 washers, site managers per location, partners, and business owners |
| **Operations** | Daily wash counts logged by managers; staff assigned to sites |

The system must support **day-to-day operations**, **financial tracking**, and **analytics for business decisions**.

---

## 2. User Roles & Permissions

| Role | Primary Users | Key Capabilities |
|------|---------------|------------------|
| **Owner / Admin** | Business owner | Full access: all sites, finances, partners, reports, settings |
| **Partner** | Business partners | View assigned sites' P&L, revenue share, performance dashboards |
| **Site Manager** | Per-mall manager | Log daily washes, manage staff attendance, submit expenses, view site KPIs |
| **Staff (Washer)** | Field workers | View own schedule, log washes (optional), view own earnings |
| **Accountant** | Finance team | Invoices, contracts, cost entry, payroll, financial reports |

---

## 3. Core Modules

### 3.1 Site & Contract Management

- Register each **mall parking location** (name, address, contact, capacity)
- Track **yearly contracts** with malls:
  - Contract value, start/end dates, renewal reminders
  - Revenue share or fixed rent terms with the mall
  - Attach contract documents
- Assign **site managers** and **staff** to locations
- Define **wash service types** per site (basic wash, premium, interior, etc.) with pricing

### 3.2 Daily Operations

- **Daily wash log** (manager enters or staff self-logs):
  - Date, site, staff member, service type, vehicle count
  - Cash vs. digital payment split
  - Shift (morning / evening)
- **Attendance & shift scheduling** for 50+ staff across sites
- **End-of-day summary** per site: total cars, revenue, staff count
- Mobile-friendly interface for managers in parking lots

### 3.3 Staff Management

- Staff profiles: name, phone, ID, role, assigned site(s), hire date, salary type (daily/monthly)
- **Housing allocation** — which staff live in company-provided accommodation
- **Food allowance** tracking per staff or per site
- Performance metrics: cars washed per day/week, attendance rate
- Payroll calculation based on washes, fixed salary, or hybrid

### 3.4 Partner Management

- Partner profiles with **ownership/revenue share %** per site or globally
- Automatic **profit distribution** calculation
- Partner portal: view revenue, costs, and net profit for their sites
- Settlement history and payout tracking

### 3.5 Financial Management

#### Revenue
- Daily revenue per site (auto-calculated from wash logs × price)
- Payment method breakdown (cash, card, UPI, mall billing)

#### Fixed Costs
| Category | Examples |
|----------|----------|
| Mall contracts | Yearly parking lease fees |
| Equipment | Wash machines, vacuums, pressure washers — purchase & depreciation |
| Accommodation | Staff housing rent |
| Office | Admin office rent |
| Insurance | Equipment & liability |

#### Variable Costs
| Category | Examples |
|----------|----------|
| Per-wash consumables | Water, shampoo, wax, microfiber towels |
| Staff food | Daily meal costs per site |
| Fuel / electricity | Generator, water pump running costs |
| Maintenance | Equipment repair per site |

- **Expense entry** with approval workflow (manager submits → admin approves)
- **Budget vs. actual** tracking per site per month

### 3.6 Equipment & Asset Management

- Asset register: equipment ID, site, purchase date, cost, warranty
- Maintenance schedule and repair history
- Depreciation calculation for P&L
- Downtime tracking (equipment out of service = lost revenue)

### 3.7 Analytics & Business Intelligence

Dashboards the owner needs for **decision-making**:

#### Operational KPIs
- Cars washed today / this week / this month (by site, by staff)
- Average cars per staff per day
- Site utilization vs. capacity
- Attendance rate by site

#### Financial KPIs
- **Revenue vs. cost** per site (daily, monthly, YTD)
- **Profit margin** per site and overall
- **Cost per wash** (variable + allocated fixed costs)
- **Break-even analysis** — how many cars/day needed to cover costs
- Contract ROI — is each mall contract profitable?

#### Comparative Analysis
- Site ranking by profitability
- Staff productivity ranking
- Month-over-month and year-over-year trends
- Seasonal patterns (festivals, weekends, mall footfall)

#### Partner Reports
- Revenue share statements
- Site-wise profit attribution

#### Alerts & Notifications
- Contract renewal due in 30/60/90 days
- Site running below break-even
- Unusual expense spikes
- Missing daily logs from managers

---

## 4. Key Business Decisions the System Should Enable

| Decision | Data Needed |
|----------|-------------|
| Should we renew a mall contract? | Site P&L over contract period, trend, break-even cars/day |
| Which sites are underperforming? | Revenue, costs, margin ranking |
| Do we need more staff at a site? | Cars/staff ratio, queue data, revenue opportunity |
| Is equipment worth replacing? | Maintenance cost vs. new purchase, downtime impact |
| How to split partner payouts? | Automated profit share based on agreed % |
| What price to charge per wash? | Cost per wash + target margin analysis |
| Where to cut costs? | Expense breakdown by category and site |

---

## 5. Implementation Phases

### Phase 1 — Foundation (Weeks 1–3)
- User auth & role-based access
- Site & contract management
- Staff registry
- Daily wash logging (manager mobile view)
- Basic dashboard: today's washes, revenue

### Phase 2 — Finance (Weeks 4–6)
- Expense tracking (fixed & variable)
- Equipment/asset register
- Monthly P&L per site
- Cost per wash calculation

### Phase 3 — Partners & Payroll (Weeks 7–9)
- Partner management & revenue share
- Staff payroll calculation
- Housing & food cost allocation

### Phase 4 — Analytics & Intelligence (Weeks 10–12)
- Advanced dashboards & charts
- Break-even analysis
- Contract renewal alerts
- Exportable reports (PDF/Excel)
- Year-over-year comparisons

### Phase 5 — Mobile & Automation (Ongoing)
- Staff mobile app for self-logging washes
- WhatsApp/SMS daily summary to owner
- Automated mall invoicing
- Integration with payment gateways

---

## 6. Data You Need to Collect from the Client

Before building, gather:

1. **List of all mall parking sites** with contract details
2. **Staff list** with roles, sites, salary structure
3. **Partner agreements** — names, share percentages, which sites
4. **Service types & pricing** at each location
5. **Historical data** (even Excel sheets): daily wash counts, expenses for last 3–6 months
6. **Cost categories** they currently track (and how)
7. **Equipment inventory** with purchase costs
8. **Preferred language** (English, Arabic, Hindi, etc.) for the UI
9. **Payment methods** used (cash-heavy? mall billing?)

---

## 7. Success Metrics

| Metric | Target |
|--------|--------|
| Daily log completion | 100% of sites logged by end of day |
| Time to enter daily data | < 5 minutes per site manager |
| Report generation | Monthly P&L available by 2nd of each month |
| Decision speed | Owner can answer "is site X profitable?" in < 30 seconds |
| Data accuracy | Revenue in system matches cash collected within 2% |
