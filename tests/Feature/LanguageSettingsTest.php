<?php

namespace Tests\Feature;

use App\Filament\Pages\LanguageSettings;
use App\Http\Middleware\SetLocale;
use Livewire\Livewire;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class LanguageSettingsTest extends TestCase
{
    use SetsUpWashBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
    }

    public function test_user_can_change_locale(): void
    {
        Livewire::actingAs($this->admin)
            ->test(LanguageSettings::class)
            ->set('locale', 'bn')
            ->call('save')
            ->assertNotified();

        $this->assertEquals('bn', $this->admin->fresh()->locale);
        $this->assertEquals('bn', session('locale'));
    }

    public function test_set_locale_middleware_applies_user_locale(): void
    {
        $this->admin->update(['locale' => 'ar']);

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk();

        $this->assertEquals('ar', app()->getLocale());
    }

    public function test_supported_locales_list(): void
    {
        $this->assertContains('en', SetLocale::LOCALES);
        $this->assertContains('bn', SetLocale::LOCALES);
        $this->assertContains('ar', SetLocale::LOCALES);
        $this->assertContains('hi', SetLocale::LOCALES);
        $this->assertContains('ur', SetLocale::LOCALES);
    }
}
