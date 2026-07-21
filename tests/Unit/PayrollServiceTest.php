<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Models\DailyLog;
use App\Models\PayrollRecord;
use App\Models\Staff;
use App\Models\StaffAssignment;
use App\Models\WashEntry;
use App\Services\PayrollService;
use Carbon\Carbon;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class PayrollServiceTest extends TestCase
{
    use SetsUpWashBusiness;

    private PayrollService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
        $this->service = new PayrollService;
    }

    public function test_daily_wage_staff_gets_base_times_days_worked(): void
    {
        $this->staff->update([
            'salary_type' => 'daily',
            'base_salary' => 500,
        ]);

        $this->createWashEntry(onDate: now()->subDays(2), vehicleCount: 5);
        $this->createWashEntry(onDate: now()->subDays(1), vehicleCount: 8);
        $this->createWashEntry(onDate: now(), vehicleCount: 3);

        $periodStart = now()->subDays(2)->startOfDay();
        $periodEnd = now()->endOfDay();

        $record = $this->service->calculateForStaff($this->staff, $periodStart, $periodEnd);

        $this->assertEquals(3, $record->days_worked);
        $this->assertEquals(1500.0, (float) $record->base_amount);
        $this->assertEquals(0.0, (float) $record->wash_bonus);
        $this->assertEquals(1500.0, (float) $record->net_amount);
        $this->assertEquals(16, $record->cars_washed);
    }

    public function test_monthly_salary_staff_gets_full_base(): void
    {
        $this->staff->update([
            'salary_type' => 'monthly',
            'base_salary' => 12000,
        ]);

        $this->createWashEntry(onDate: now(), vehicleCount: 10);

        $record = $this->service->calculateForStaff(
            $this->staff,
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        $this->assertEquals(12000.0, (float) $record->base_amount);
        $this->assertEquals(12000.0, (float) $record->net_amount);
    }

    public function test_per_car_staff_gets_rate_times_cars(): void
    {
        $this->staff->update([
            'salary_type' => 'per_car',
            'base_salary' => null,
            'per_wash_rate' => 15,
        ]);

        $this->createWashEntry(onDate: now(), vehicleCount: 20);

        $record = $this->service->calculateForStaff(
            $this->staff,
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        $this->assertEquals(0.0, (float) $record->base_amount);
        $this->assertEquals(300.0, (float) $record->wash_bonus);
        $this->assertEquals(300.0, (float) $record->net_amount);
        $this->assertEquals(20, $record->cars_washed);
    }

    public function test_hybrid_staff_gets_base_plus_per_car_bonus(): void
    {
        $this->staff->update([
            'salary_type' => 'hybrid',
            'base_salary' => 8000,
            'per_wash_rate' => 10,
        ]);

        $this->createWashEntry(onDate: now(), vehicleCount: 50);

        $record = $this->service->calculateForStaff(
            $this->staff,
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        $this->assertEquals(8000.0, (float) $record->base_amount);
        $this->assertEquals(500.0, (float) $record->wash_bonus);
        $this->assertEquals(8500.0, (float) $record->net_amount);
    }

    public function test_generate_for_site_creates_records_for_all_staff(): void
    {
        $second = Staff::create([
            'organization_id' => $this->organization->id,
            'name' => 'Second Washer',
            'staff_type' => 'washer',
            'salary_type' => 'daily',
            'base_salary' => 400,
            'is_active' => true,
        ]);

        StaffAssignment::create([
            'staff_id' => $second->id,
            'site_id' => $this->site->id,
            'is_primary' => true,
            'start_date' => now()->toDateString(),
        ]);

        $this->staff->update(['salary_type' => 'daily', 'base_salary' => 500]);
        $this->createWashEntry(onDate: now(), vehicleCount: 5, staffId: $this->staff->id);
        $this->createWashEntry(onDate: now(), vehicleCount: 3, staffId: $second->id);

        $records = $this->service->generateForSite(
            $this->site,
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        $this->assertCount(2, $records);
        $this->assertEquals(2, PayrollRecord::count());
    }

    public function test_mark_payroll_as_paid(): void
    {
        $this->staff->update(['salary_type' => 'monthly', 'base_salary' => 10000]);

        $record = $this->service->calculateForStaff(
            $this->staff,
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        $paid = $this->service->markPaid($record);

        $this->assertNotNull($paid->paid_at);
    }

    private function createWashEntry(
        Carbon $onDate,
        int $vehicleCount,
        ?int $staffId = null,
    ): WashEntry {
        $staffId ??= $this->staff->id;

        $dailyLog = DailyLog::query()
            ->where('site_id', $this->site->id)
            ->whereDate('date', $onDate->toDateString())
            ->where('shift', Shift::Morning->value)
            ->first();

        if (! $dailyLog) {
            $dailyLog = DailyLog::create([
                'site_id' => $this->site->id,
                'date' => $onDate->toDateString(),
                'shift' => Shift::Morning->value,
                'submitted_by_id' => $this->manager->id,
            ]);
        }

        return WashEntry::create([
            'daily_log_id' => $dailyLog->id,
            'staff_id' => $staffId,
            'service_type_id' => $this->serviceType->id,
            'vehicle_count' => $vehicleCount,
            'payment_method' => PaymentMethod::Cash,
        ]);
    }
}
