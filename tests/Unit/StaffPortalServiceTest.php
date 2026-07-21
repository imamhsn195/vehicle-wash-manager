<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Models\DailyLog;
use App\Models\WashEntry;
use App\Services\StaffPortalService;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class StaffPortalServiceTest extends TestCase
{
    use SetsUpWashBusiness;

    private StaffPortalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
        $this->staff->update([
            'user_id' => null,
            'salary_type' => 'per_car',
            'per_wash_rate' => 15,
        ]);
        $this->service = new StaffPortalService;
    }

    public function test_it_returns_cars_washed_today(): void
    {
        $this->createWashEntry(today(), 8);
        $this->createWashEntry(today()->subDay(), 20);

        $stats = $this->service->statsFor($this->staff);

        $this->assertEquals(8, $stats['cars_today']);
        $this->assertEquals(20, $stats['cars_yesterday']);
    }

    public function test_it_returns_cars_this_month(): void
    {
        $this->createWashEntry(today(), 5);
        $this->createWashEntry(today()->subDays(3), 10);

        $stats = $this->service->statsFor($this->staff);

        $this->assertEquals(15, $stats['cars_this_month']);
    }

    public function test_estimated_earnings_for_per_car_staff(): void
    {
        $this->createWashEntry(today(), 10);

        $stats = $this->service->statsFor($this->staff);

        $this->assertEquals(150.0, $stats['estimated_earnings_today']);
        $this->assertEquals(150.0, $stats['estimated_earnings_month']);
    }

    public function test_estimated_earnings_for_daily_staff(): void
    {
        $this->staff->update([
            'salary_type' => 'daily',
            'base_salary' => 500,
            'per_wash_rate' => null,
        ]);

        $this->createWashEntry(today(), 5);

        $stats = $this->service->statsFor($this->staff);

        $this->assertEquals(500.0, $stats['estimated_earnings_today']);
    }

    public function test_find_staff_by_user(): void
    {
        $this->staff->update(['user_id' => $this->manager->id]);

        $found = $this->service->staffForUser($this->manager);

        $this->assertNotNull($found);
        $this->assertEquals($this->staff->id, $found->id);
    }

    private function createWashEntry($date, int $vehicleCount): WashEntry
    {
        $date = \Illuminate\Support\Carbon::parse($date);

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
