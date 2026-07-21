<?php

namespace Tests\Unit;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseType;
use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Models\Contract;
use App\Models\DailyLog;
use App\Models\WashEntry;
use App\Services\BreakEvenService;
use App\Services\ExpenseService;
use App\Services\PnLService;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class BreakEvenServiceTest extends TestCase
{
    use SetsUpWashBusiness;

    private BreakEvenService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();

        Contract::create([
            'site_id' => $this->site->id,
            'title' => 'Lease',
            'annual_value' => 365000, // ~1000/day
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'status' => 'active',
        ]);

        $this->staff->update(['daily_food_allowance' => 100]);

        $this->service = new BreakEvenService(new PnLService(new ExpenseService));
    }

    public function test_break_even_cars_per_day(): void
    {
        // Price 200, variable cost ≈ food 100/day / cars... we use avg variable from expenses
        // Fixed daily = mall 365000/365 = 1000 + food 100 = 1100
        // Contribution = price - variable_per_wash
        // With no recorded variable expenses, variable_per_wash = 0
        // Break-even = 1100 / 200 = 5.5 → ceil 6

        $result = $this->service->forSite($this->site, now()->year, now()->month);

        $this->assertEquals(200.0, $result['avg_price']);
        $this->assertGreaterThan(0, $result['daily_fixed_cost']);
        $this->assertEquals(6, $result['break_even_cars']);
        $this->assertTrue($result['is_finite']);
    }

    public function test_break_even_is_infinite_when_contribution_non_positive(): void
    {
        $this->serviceType->update(['price' => 0]);

        $result = $this->service->forSite($this->site, now()->year, now()->month);

        $this->assertFalse($result['is_finite']);
        $this->assertNull($result['break_even_cars']);
    }

    public function test_site_below_break_even_when_avg_daily_cars_low(): void
    {
        // 2 cars today only → well below break-even of ~6
        $this->createWashEntry(2);

        $result = $this->service->forSite($this->site, now()->year, now()->month);

        $this->assertTrue($result['is_below_break_even']);
        $this->assertLessThan($result['break_even_cars'], $result['avg_daily_cars']);
    }

    public function test_site_above_break_even_with_enough_cars(): void
    {
        foreach (range(1, 20) as $day) {
            $this->createWashEntry(20, now()->subDays($day - 1));
        }

        $result = $this->service->forSite($this->site, now()->year, now()->month);

        $this->assertFalse($result['is_below_break_even']);
        $this->assertGreaterThanOrEqual($result['break_even_cars'], $result['avg_daily_cars']);
    }

    private function createWashEntry(int $vehicleCount, $date = null): WashEntry
    {
        $date = $date ? \Illuminate\Support\Carbon::parse($date) : now();

        $dailyLog = DailyLog::query()
            ->where('site_id', $this->site->id)
            ->whereDate('date', $date->toDateString())
            ->where('shift', Shift::Morning->value)
            ->first();

        if (! $dailyLog) {
            $dailyLog = DailyLog::create([
                'site_id' => $this->site->id,
                'date' => $date->toDateString(),
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
