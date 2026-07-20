<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Models\DailyLog;
use App\Models\ServiceType;
use App\Models\WashEntry;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use SetsUpWashBusiness;

    private AnalyticsService $analytics;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
        $this->analytics = new AnalyticsService;
    }

    public function test_cars_today_returns_zero_when_no_entries(): void
    {
        $this->assertEquals(0, $this->analytics->carsToday());
    }

    public function test_cars_today_sums_vehicle_counts(): void
    {
        $this->createWashEntry(vehicleCount: 10);
        $this->createWashEntry(vehicleCount: 15);

        $this->assertEquals(25, $this->analytics->carsToday());
    }

    public function test_cars_today_can_filter_by_site(): void
    {
        $otherSite = \App\Models\Site::create([
            'organization_id' => $this->organization->id,
            'name' => 'Other Mall',
            'mall_name' => 'Other Mall',
            'is_active' => true,
        ]);

        $this->createWashEntry(vehicleCount: 10);
        $this->createWashEntry(vehicleCount: 99, siteId: $otherSite->id);

        $this->assertEquals(10, $this->analytics->carsToday($this->site->id));
    }

    public function test_revenue_today_calculates_from_price_and_count(): void
    {
        $this->createWashEntry(vehicleCount: 10);
        $this->createWashEntry(vehicleCount: 5);

        $this->assertEquals(3000.0, $this->analytics->revenueToday());
    }

    public function test_revenue_by_site_today_groups_correctly(): void
    {
        $this->createWashEntry(vehicleCount: 10);

        $results = $this->analytics->revenueBySiteToday();

        $this->assertCount(1, $results);
        $this->assertEquals('Test Mall', $results->first()->site_name);
        $this->assertEquals(10, $results->first()->cars);
        $this->assertEquals(2000, $results->first()->revenue);
    }

    public function test_staff_productivity_today_ranks_by_cars_washed(): void
    {
        $staffTwo = \App\Models\Staff::create([
            'organization_id' => $this->organization->id,
            'name' => 'Second Washer',
            'staff_type' => 'washer',
            'salary_type' => 'daily',
            'is_active' => true,
        ]);

        \App\Models\StaffAssignment::create([
            'staff_id' => $staffTwo->id,
            'site_id' => $this->site->id,
            'is_primary' => true,
            'start_date' => now()->toDateString(),
        ]);

        $this->createWashEntry(vehicleCount: 5);
        $this->createWashEntry(vehicleCount: 20, staffId: $staffTwo->id);

        $results = $this->analytics->staffProductivityToday();

        $this->assertEquals('Second Washer', $results->first()->name);
        $this->assertEquals(20, $results->first()->cars);
    }

    public function test_ignores_yesterdays_entries(): void
    {
        $this->createWashEntry(vehicleCount: 10, date: now()->subDay());

        $this->assertEquals(0, $this->analytics->carsToday());
        $this->assertEquals(0.0, $this->analytics->revenueToday());
    }

    private function createWashEntry(
        int $vehicleCount = 5,
        ?int $staffId = null,
        ?int $siteId = null,
        ?\Illuminate\Support\Carbon $date = null,
    ): WashEntry {
        $siteId ??= $this->site->id;
        $staffId ??= $this->staff->id;
        $date ??= now();

        $serviceType = ServiceType::where('site_id', $siteId)->first()
            ?? ServiceType::create([
                'site_id' => $siteId,
                'name' => 'Standard Wash',
                'price' => 200,
                'is_active' => true,
            ]);

        $dailyLog = DailyLog::query()
            ->where('site_id', $siteId)
            ->where('shift', Shift::Morning->value)
            ->whereDate('date', $date->toDateString())
            ->first();

        if (! $dailyLog) {
            $dailyLog = DailyLog::create([
                'site_id' => $siteId,
                'date' => $date->toDateString(),
                'shift' => Shift::Morning->value,
                'submitted_by_id' => $this->manager->id,
                'is_closed' => true,
            ]);
        }

        return WashEntry::create([
            'daily_log_id' => $dailyLog->id,
            'staff_id' => $staffId,
            'service_type_id' => $serviceType->id,
            'vehicle_count' => $vehicleCount,
            'payment_method' => PaymentMethod::Cash,
        ]);
    }
}
