<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\CurrencySettings;
use App\Filament\Pages\DailyLogEntry;
use App\Filament\Pages\DataExports;
use App\Filament\Pages\PartnerPayoutReport;
use App\Filament\Resources\ExpenseResource;
use App\Filament\Resources\PartnerResource;
use App\Filament\Resources\PayrollRecordResource;
use App\Filament\Resources\SiteResource;
use App\Models\Partner;
use App\Models\User;
use App\Support\FilamentAccess;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class FilamentAccessTest extends TestCase
{
    use SetsUpWashBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
    }

    public function test_staff_cannot_access_sites_or_payroll(): void
    {
        $staffUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Staff,
        ]);
        $this->staff->update(['user_id' => $staffUser->id]);

        $this->actingAs($staffUser);

        $this->assertTrue(FilamentAccess::canAccessDailyLogEntry());
        $this->assertFalse(SiteResource::canAccess());
        $this->assertFalse(PayrollRecordResource::canAccess());
        $this->assertFalse(CurrencySettings::canAccess());
        $this->assertFalse(DataExports::canAccess());
    }

    public function test_partner_only_sees_own_partner_and_payouts(): void
    {
        $partnerUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Partner,
        ]);
        Partner::create([
            'organization_id' => $this->organization->id,
            'user_id' => $partnerUser->id,
            'name' => 'Scoped Partner',
            'is_active' => true,
        ]);

        $this->actingAs($partnerUser);

        $this->assertTrue(PartnerPayoutReport::canAccess());
        $this->assertTrue(PartnerResource::canAccess());
        $this->assertFalse(PartnerResource::canCreate());
        $this->assertFalse(SiteResource::canAccess());
        $this->assertFalse(ExpenseResource::canAccess());
        $this->assertFalse(DailyLogEntry::canAccess());
    }

    public function test_site_manager_is_scoped_to_managed_sites(): void
    {
        $this->actingAs($this->manager);

        $this->assertTrue(SiteResource::canAccess());
        $this->assertTrue(ExpenseResource::canAccess());
        $this->assertFalse(PayrollRecordResource::canAccess());
        $this->assertFalse(CurrencySettings::canAccess());
        $this->assertEquals([$this->site->id], FilamentAccess::managedSiteIds());
        $this->assertTrue(
            SiteResource::getEloquentQuery()->whereKey($this->site->id)->exists()
        );
    }

    public function test_accountant_can_approve_expenses_but_not_currency(): void
    {
        $accountant = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Accountant,
        ]);
        $this->actingAs($accountant);

        $this->assertTrue(ExpenseResource::canAccess());
        $this->assertTrue(FilamentAccess::canApproveExpenses());
        $this->assertTrue(DataExports::canAccess());
        $this->assertFalse(CurrencySettings::canAccess());
    }

    public function test_role_and_is_active_are_not_mass_assignable(): void
    {
        $user = User::create([
            'organization_id' => $this->organization->id,
            'name' => 'Mass Assign',
            'email' => 'mass@test.test',
            'password' => 'password',
            'role' => UserRole::Admin,
            'is_active' => false,
        ]);

        $this->assertNotEquals(UserRole::Admin, $user->fresh()->role);
        $this->assertTrue((bool) $user->fresh()->is_active);
    }

    public function test_deploy_branch_defaults_to_main(): void
    {
        $this->assertSame('main', config('deploy.branch'));
    }

    public function test_deploy_webhook_is_csrf_exempt(): void
    {
        $ref = new \ReflectionClass(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
        $prop = $ref->getProperty('neverVerify');
        $prop->setAccessible(true);
        $except = $prop->getValue();

        $this->assertTrue(
            collect($except)->contains(fn ($uri) => str_contains((string) $uri, 'deploy/webhook'))
        );
    }
}
