<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Livewire\LocaleSwitcher;
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

    public function test_user_can_change_locale_from_switcher(): void
    {
        Livewire::actingAs($this->admin)
            ->test(LocaleSwitcher::class)
            ->call('setLocale', 'bn')
            ->assertRedirect();

        $this->assertEquals('bn', $this->admin->fresh()->locale);
        $this->assertEquals('bn', session('locale'));
    }

    public function test_locale_is_saved_per_user(): void
    {
        Livewire::actingAs($this->admin)
            ->test(LocaleSwitcher::class)
            ->call('setLocale', 'hi');

        Livewire::actingAs($this->manager)
            ->test(LocaleSwitcher::class)
            ->call('setLocale', 'ar');

        $this->assertEquals('hi', $this->admin->fresh()->locale);
        $this->assertEquals('ar', $this->manager->fresh()->locale);
    }

    public function test_set_locale_middleware_applies_user_locale(): void
    {
        $this->admin->update(['locale' => 'ar']);

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk();

        $this->assertEquals('ar', app()->getLocale());
    }

    public function test_language_settings_page_is_removed(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/language-settings')
            ->assertNotFound();
    }

    public function test_supported_locales_list(): void
    {
        $this->assertContains('en', SetLocale::LOCALES);
        $this->assertContains('bn', SetLocale::LOCALES);
        $this->assertContains('ar', SetLocale::LOCALES);
        $this->assertContains('hi', SetLocale::LOCALES);
        $this->assertContains('ur', SetLocale::LOCALES);
        $this->assertEquals('বাংলা', SetLocale::label('bn'));
    }
}
