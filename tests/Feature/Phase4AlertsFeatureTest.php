<?php

namespace Tests\Feature;

use App\Filament\Pages\BreakEvenAnalysis;
use App\Filament\Widgets\AlertsWidget;
use App\Models\Contract;
use Livewire\Livewire;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class Phase4AlertsFeatureTest extends TestCase
{
    use SetsUpWashBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
    }

    public function test_break_even_page_loads(): void
    {
        Contract::create([
            'site_id' => $this->site->id,
            'title' => 'Lease',
            'annual_value' => 120000,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'status' => 'active',
        ]);

        Livewire::actingAs($this->admin)
            ->test(BreakEvenAnalysis::class)
            ->assertOk()
            ->assertSee('Test Mall');
    }

    public function test_alerts_widget_shows_renewal_and_missing_log(): void
    {
        Contract::create([
            'site_id' => $this->site->id,
            'title' => 'Soon Expiring Lease',
            'annual_value' => 100000,
            'start_date' => now()->subYear(),
            'end_date' => now()->addDays(15),
            'status' => 'active',
            'renewal_reminder_days' => 60,
        ]);

        Livewire::actingAs($this->admin)
            ->test(AlertsWidget::class)
            ->assertSee('Soon Expiring Lease')
            ->assertSee('Test Mall')
            ->assertSee('No log today');
    }
}
