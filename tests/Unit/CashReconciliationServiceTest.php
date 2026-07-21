<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Models\DailyLog;
use App\Models\WashEntry;
use App\Services\CashReconciliationService;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class CashReconciliationServiceTest extends TestCase
{
    use SetsUpWashBusiness;

    private CashReconciliationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
        $this->service = new CashReconciliationService;
    }

    public function test_expected_revenue_from_wash_logs(): void
    {
        $this->createWashEntry(10);
        $this->createWashEntry(5);

        $expected = $this->service->expectedRevenue($this->site->id, now()->toDateString());

        $this->assertEquals(3000.0, $expected);
    }

    public function test_it_records_reconciliation(): void
    {
        $this->createWashEntry(10);

        $record = $this->service->record([
            'site_id' => $this->site->id,
            'date' => now()->toDateString(),
            'cash_collected' => 2000,
            'deposited_amount' => 2000,
            'is_deposited' => true,
        ], $this->manager);

        $this->assertEquals(2000.0, (float) $record->expected_revenue);
        $this->assertEquals(2000.0, (float) $record->cash_collected);
        $this->assertTrue($record->is_deposited);
        $this->assertEquals(0.0, $record->difference());
    }

    public function test_difference_flags_shortfall(): void
    {
        $this->createWashEntry(10);

        $record = $this->service->record([
            'site_id' => $this->site->id,
            'date' => now()->toDateString(),
            'cash_collected' => 1800,
            'deposited_amount' => 1800,
            'is_deposited' => true,
        ], $this->manager);

        $this->assertEquals(-200.0, $record->difference());
        $this->assertTrue($record->hasDiscrepancy());
    }

    public function test_updates_existing_reconciliation_for_same_site_date(): void
    {
        $this->createWashEntry(5);

        $this->service->record([
            'site_id' => $this->site->id,
            'date' => now()->toDateString(),
            'cash_collected' => 500,
            'deposited_amount' => 500,
            'is_deposited' => false,
        ], $this->manager);

        $updated = $this->service->record([
            'site_id' => $this->site->id,
            'date' => now()->toDateString(),
            'cash_collected' => 1000,
            'deposited_amount' => 1000,
            'is_deposited' => true,
        ], $this->manager);

        $this->assertEquals(1, \App\Models\CashReconciliation::count());
        $this->assertEquals(1000.0, (float) $updated->cash_collected);
        $this->assertTrue($updated->is_deposited);
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
