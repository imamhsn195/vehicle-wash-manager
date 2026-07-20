<?php

namespace Tests\Feature;

use App\Enums\Shift;
use App\Filament\Pages\DailyLogEntry;
use App\Models\WashEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class QuickDailyLogPageTest extends TestCase
{
    use SetsUpWashBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
    }

    public function test_manager_can_submit_wash_entry(): void
    {
        Livewire::actingAs($this->manager)
            ->test(DailyLogEntry::class)
            ->fillForm($this->validEntryData())
            ->call('submit')
            ->assertNotified();

        $this->assertEquals(1, WashEntry::count());
        $this->assertEquals(5, WashEntry::first()->vehicle_count);
    }

    public function test_admin_can_submit_for_any_site(): void
    {
        Livewire::actingAs($this->admin)
            ->test(DailyLogEntry::class)
            ->fillForm($this->validEntryData(['vehicle_count' => 12]))
            ->call('submit')
            ->assertNotified();

        $this->assertEquals(12, WashEntry::first()->vehicle_count);
    }

    public function test_submit_fails_gracefully_without_service_type(): void
    {
        $this->serviceType->update(['is_active' => false]);

        Livewire::actingAs($this->manager)
            ->test(DailyLogEntry::class)
            ->fillForm($this->validEntryData())
            ->call('submit')
            ->assertNotified();

        $this->assertEquals(0, WashEntry::count());
    }

    public function test_multiple_entries_accumulate_on_same_log(): void
    {
        Livewire::actingAs($this->manager)
            ->test(DailyLogEntry::class)
            ->fillForm($this->validEntryData(['vehicle_count' => 3]))
            ->call('submit')
            ->fillForm($this->validEntryData(['vehicle_count' => 7]))
            ->call('submit');

        $this->assertEquals(2, WashEntry::count());
        $this->assertEquals(10, WashEntry::sum('vehicle_count'));
    }
}
