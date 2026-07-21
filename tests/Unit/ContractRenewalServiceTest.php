<?php

namespace Tests\Unit;

use App\Models\Contract;
use App\Services\ContractRenewalService;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class ContractRenewalServiceTest extends TestCase
{
    use SetsUpWashBusiness;

    private ContractRenewalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
        $this->service = new ContractRenewalService;
    }

    public function test_it_finds_contracts_expiring_within_reminder_window(): void
    {
        Contract::create([
            'site_id' => $this->site->id,
            'title' => 'Expiring Soon',
            'annual_value' => 100000,
            'start_date' => now()->subYear(),
            'end_date' => now()->addDays(30),
            'status' => 'active',
            'renewal_reminder_days' => 60,
        ]);

        $alerts = $this->service->upcomingRenewals();

        $this->assertCount(1, $alerts);
        $this->assertEquals('Expiring Soon', $alerts->first()->title);
        $this->assertLessThanOrEqual(60, $alerts->first()->days_until_expiry);
    }

    public function test_it_ignores_contracts_outside_reminder_window(): void
    {
        Contract::create([
            'site_id' => $this->site->id,
            'title' => 'Far Away',
            'annual_value' => 100000,
            'start_date' => now()->subMonths(1),
            'end_date' => now()->addDays(200),
            'status' => 'active',
            'renewal_reminder_days' => 60,
        ]);

        $alerts = $this->service->upcomingRenewals();

        $this->assertCount(0, $alerts);
    }

    public function test_it_includes_already_expired_active_contracts(): void
    {
        Contract::create([
            'site_id' => $this->site->id,
            'title' => 'Overdue',
            'annual_value' => 100000,
            'start_date' => now()->subYears(2),
            'end_date' => now()->subDays(5),
            'status' => 'active',
            'renewal_reminder_days' => 60,
        ]);

        $alerts = $this->service->upcomingRenewals();

        $this->assertCount(1, $alerts);
        $this->assertTrue($alerts->first()->is_overdue);
    }

    public function test_it_marks_pending_renewal_status(): void
    {
        $contract = Contract::create([
            'site_id' => $this->site->id,
            'title' => 'Needs Status Update',
            'annual_value' => 100000,
            'start_date' => now()->subYear(),
            'end_date' => now()->addDays(20),
            'status' => 'active',
            'renewal_reminder_days' => 60,
        ]);

        $updated = $this->service->flagPendingRenewals();

        $this->assertEquals(1, $updated);
        $this->assertEquals('pending_renewal', $contract->fresh()->status);
    }

    public function test_missing_daily_logs_detects_sites_without_today_entry(): void
    {
        $missing = $this->service->sitesMissingTodayLog();

        $this->assertTrue($missing->contains('id', $this->site->id));
    }
}
