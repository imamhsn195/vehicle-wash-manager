# Test-Driven Development (TDD)

This project uses **TDD** for all business logic and features.

## Workflow

```
1. Red   → Write a failing test
2. Green → Write minimal code to pass
3. Refactor → Clean up, keep tests green
```

## Test structure

```
tests/
├── Unit/           # Services, models, pure logic (no HTTP)
├── Feature/        # HTTP, Filament pages, integration
└── Concerns/       # Shared test helpers (SetsUpWashBusiness)
```

## What to test where

| Layer | Test type | Example |
|-------|-----------|---------|
| `DailyLogService` | Unit | Record entry, reuse log, validation |
| `AnalyticsService` | Unit | Revenue, cars today, productivity |
| `WashEntry` model | Unit | Revenue calculation |
| Filament pages | Feature | Livewire submit, notifications |
| Auth / roles | Feature | Panel access by role |

## Running tests

```bash
# All tests
php artisan test

# Single file
php artisan test tests/Unit/DailyLogServiceTest.php

# Filter by name
php artisan test --filter=test_it_creates_daily_log
```

## TDD rules for this project

1. **No business logic in Filament resources/pages** — extract to `app/Services/`
2. **Write the test before the service method**
3. **Use `SetsUpWashBusiness` trait** for domain test data (site, staff, service type)
4. **Use in-memory SQLite** (configured in `phpunit.xml`) — fast, isolated
5. **Commit tests with the code they cover** — one logical part per commit

## Example: adding a new feature

```php
// 1. RED — tests/Unit/ExpenseServiceTest.php
public function test_it_calculates_monthly_fixed_costs(): void
{
    $this->assertEquals(50000, $this->service->monthlyFixedCosts($this->site));
}

// 2. GREEN — app/Services/ExpenseService.php
public function monthlyFixedCosts(Site $site): float { ... }

// 3. REFACTOR — wire into Filament resource, keep tests green
```

## Current test coverage

| Area | Tests |
|------|-------|
| Daily log submission | `DailyLogServiceTest`, `QuickDailyLogPageTest` |
| Analytics / dashboard | `AnalyticsServiceTest` |
| Revenue calculation | `WashEntryTest` |
| Panel access | `FilamentAccessTest` |
