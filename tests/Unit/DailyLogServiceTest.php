<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Exceptions\NoActiveServiceTypeException;
use App\Models\DailyLog;
use App\Models\WashEntry;
use App\Services\DailyLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class DailyLogServiceTest extends TestCase
{
    use SetsUpWashBusiness;

    private DailyLogService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
        $this->service = new DailyLogService;
    }

    public function test_it_creates_daily_log_and_wash_entry(): void
    {
        $entry = $this->service->recordEntry($this->validEntryData(), $this->manager);

        $this->assertInstanceOf(WashEntry::class, $entry);
        $this->assertDatabaseHas('daily_logs', [
            'site_id' => $this->site->id,
            'shift' => Shift::Morning->value,
            'submitted_by_id' => $this->manager->id,
        ]);
        $this->assertDatabaseHas('wash_entries', [
            'staff_id' => $this->staff->id,
            'vehicle_count' => 5,
            'payment_method' => PaymentMethod::Cash->value,
        ]);
    }

    public function test_it_reuses_existing_daily_log_for_same_site_date_shift(): void
    {
        $this->service->recordEntry($this->validEntryData(['vehicle_count' => 3]), $this->manager);
        $this->service->recordEntry($this->validEntryData(['vehicle_count' => 7]), $this->manager);

        $this->assertEquals(1, DailyLog::count());
        $this->assertEquals(2, WashEntry::count());
        $this->assertEquals(10, DailyLog::first()->totalCars());
    }

    public function test_it_creates_separate_logs_for_different_shifts(): void
    {
        $this->service->recordEntry($this->validEntryData(['shift' => Shift::Morning->value]), $this->manager);
        $this->service->recordEntry($this->validEntryData(['shift' => Shift::Evening->value]), $this->manager);

        $this->assertEquals(2, DailyLog::count());
    }

    public function test_it_throws_when_site_has_no_active_service_type(): void
    {
        $this->serviceType->update(['is_active' => false]);

        $this->expectException(NoActiveServiceTypeException::class);

        $this->service->recordEntry($this->validEntryData(), $this->manager);
    }

    public function test_it_uses_explicit_service_type_when_provided(): void
    {
        $premium = \App\Models\ServiceType::create([
            'site_id' => $this->site->id,
            'name' => 'Premium Wash',
            'price' => 350,
            'is_active' => true,
        ]);

        $entry = $this->service->recordEntry(
            $this->validEntryData(['service_type_id' => $premium->id]),
            $this->manager
        );

        $this->assertEquals($premium->id, $entry->service_type_id);
        $this->assertEquals(350.0, $entry->fresh()->serviceType->price);
    }
}
