<?php

namespace Tests\Feature;

use App\Filament\Pages\CurrencySettings;
use App\Support\Currency;
use Livewire\Livewire;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class CurrencySettingsTest extends TestCase
{
    use SetsUpWashBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
    }

    public function test_default_currency_is_bdt(): void
    {
        $this->actingAs($this->admin);

        $this->assertEquals('BDT', Currency::code());
        $this->assertEquals('৳', Currency::symbol());
        $this->assertEquals('৳200', Currency::format(200));
    }

    public function test_admin_can_change_organization_currency(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CurrencySettings::class)
            ->fillForm(['currency_code' => 'USD'])
            ->call('save')
            ->assertNotified();

        $this->assertEquals('USD', $this->organization->fresh()->currency_code);

        $this->actingAs($this->admin);
        $this->assertEquals('USD', Currency::code());
        $this->assertEquals('$', Currency::symbol());
        $this->assertEquals('$1,250.00', Currency::format(1250));
    }

    public function test_non_admin_cannot_access_currency_settings(): void
    {
        $this->assertFalse(CurrencySettings::canAccess());

        $this->actingAs($this->manager);
        $this->assertFalse(CurrencySettings::canAccess());
    }

    public function test_helpers_follow_organization_currency(): void
    {
        $this->organization->update(['currency_code' => 'INR']);
        $this->actingAs($this->admin);

        $this->assertEquals('INR', currency_code());
        $this->assertEquals('₹', currency_symbol());
        $this->assertEquals('₹500', money_format_app(500));
    }
}
