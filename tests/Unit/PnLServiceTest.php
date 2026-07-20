<?php

namespace Tests\Unit;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseType;
use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Models\Contract;
use App\Models\DailyLog;
use App\Models\WashEntry;
use App\Services\ExpenseService;
use App\Services\PnLService;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class PnLServiceTest extends TestCase
{
    use SetsUpWashBusiness;

    private PnLService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
        $this->service = new PnLService(new ExpenseService);
    }

    public function test_site_pnl_with_revenue_and_expenses(): void
    {
        $this->createWashEntry(10);
        $this->createWashEntry(5);

        $expenseService = new ExpenseService;
        $expense = $expenseService->submit([
            'organization_id' => $this->organization->id,
            'site_id' => $this->site->id,
            'type' => ExpenseType::Variable->value,
            'category' => ExpenseCategory::Consumables->value,
            'description' => 'Chemicals',
            'amount' => 500,
            'date' => now()->toDateString(),
        ], $this->manager);
        $expenseService->approve($expense, $this->admin);

        $pnl = $this->service->siteMonthlyPnL($this->site, now()->year, now()->month);

        $this->assertEquals(3000.0, $pnl['revenue']);
        $this->assertEquals(15, $pnl['cars']);
        $this->assertEquals(500.0, $pnl['expenses']);
        $this->assertEquals(2500.0, $pnl['profit']);
        $this->assertEqualsWithDelta(83.33, $pnl['margin_pct'], 0.1);
        $this->assertEqualsWithDelta(33.33, $pnl['cost_per_wash'], 0.1);
    }

    public function test_site_pnl_includes_allocated_mall_contract(): void
    {
        Contract::create([
            'site_id' => $this->site->id,
            'title' => 'Annual Lease',
            'annual_value' => 120000,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'status' => 'active',
        ]);

        $this->createWashEntry(10);

        $pnl = $this->service->siteMonthlyPnL($this->site, now()->year, now()->month);

        $this->assertEquals(2000.0, $pnl['revenue']);
        $this->assertEquals(10000.0, $pnl['mall_contract_allocated']);
        $this->assertEquals(10000.0, $pnl['expenses']);
        $this->assertEquals(-8000.0, $pnl['profit']);
    }

    public function test_cost_per_wash_is_zero_when_no_cars(): void
    {
        $pnl = $this->service->siteMonthlyPnL($this->site, now()->year, now()->month);

        $this->assertEquals(0.0, $pnl['cost_per_wash']);
        $this->assertEquals(0.0, $pnl['margin_pct']);
    }

    public function test_staff_food_cost_auto_calculated(): void
    {
        $this->staff->update(['daily_food_allowance' => 100]);

        $foodCost = $this->service->staffFoodCostForSite(
            $this->site,
            now()->year,
            now()->month
        );

        $daysInMonth = now()->daysInMonth;
        $this->assertEquals(100.0 * $daysInMonth, $foodCost);
    }

    private function createWashEntry(int $vehicleCount): WashEntry
    {
        $dailyLog = DailyLog::query()
            ->where('site_id', $this->site->id)
            ->whereDate('date', now()->toDateString())
            ->where('shift', Shift::Morning->value)
            ->first();

        if (! $dailyLog) {
            $dailyLog = DailyLog::create([
                'site_id' => $this->site->id,
                'date' => now()->toDateString(),
                'shift' => Shift::Morning->value,
                'submitted_by_id' => $this->manager->id,
            ]);
        }

        return WashEntry::create([
            'daily_log_id' => $dailyLog->id,
            'staff_id' => $this->staff->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_count' => $vehicleCount,
            'payment_method' => PaymentMethod::Cash,
        ]);
    }
}
