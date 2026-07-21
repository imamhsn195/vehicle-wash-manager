<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Models\DailyLog;
use App\Models\WashEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class WashEntryTest extends TestCase
{
    use SetsUpWashBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
    }

    public function test_revenue_uses_service_price_when_amount_is_null(): void
    {
        $entry = $this->makeEntry(vehicleCount: 10, amount: null);

        $this->assertEquals(2000.0, $entry->revenue());
    }

    public function test_revenue_uses_override_amount_when_set(): void
    {
        $entry = $this->makeEntry(vehicleCount: 10, amount: 250);

        $this->assertEquals(2500.0, $entry->revenue());
    }

    private function makeEntry(int $vehicleCount, ?float $amount): WashEntry
    {
        $dailyLog = DailyLog::create([
            'site_id' => $this->site->id,
            'date' => now()->toDateString(),
            'shift' => Shift::Morning,
            'is_closed' => false,
        ]);

        return WashEntry::create([
            'daily_log_id' => $dailyLog->id,
            'staff_id' => $this->staff->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_count' => $vehicleCount,
            'payment_method' => PaymentMethod::Cash,
            'amount' => $amount,
        ]);
    }
}
