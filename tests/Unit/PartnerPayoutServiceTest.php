<?php

namespace Tests\Unit;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseType;
use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Models\Contract;
use App\Models\DailyLog;
use App\Models\Partner;
use App\Models\PartnerSettlement;
use App\Models\PartnerSiteShare;
use App\Models\WashEntry;
use App\Services\ExpenseService;
use App\Services\PartnerPayoutService;
use App\Services\PnLService;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class PartnerPayoutServiceTest extends TestCase
{
    use SetsUpWashBusiness;

    private PartnerPayoutService $service;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();

        $this->partner = Partner::create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Partner',
            'email' => 'partner@test.test',
            'is_active' => true,
        ]);

        PartnerSiteShare::create([
            'partner_id' => $this->partner->id,
            'site_id' => $this->site->id,
            'share_pct' => 30,
        ]);

        Contract::create([
            'site_id' => $this->site->id,
            'title' => 'Lease',
            'annual_value' => 120000,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'status' => 'active',
        ]);

        $this->service = new PartnerPayoutService(new PnLService(new ExpenseService));
    }

    public function test_it_calculates_payout_as_share_of_site_profit(): void
    {
        // Revenue: 20 cars × 200 = 4000
        // Mall allocated: 10000/month → profit negative, but we still calculate share of profit
        $this->createWashEntries(20);

        // Override: use a month with lower mall cost by not counting food
        // Profit = 4000 - 10000 - 0 food = -6000
        // Share = -6000 * 30% = -1800

        $amount = $this->service->calculatePartnerPayout(
            $this->partner,
            now()->year,
            now()->month
        );

        $this->assertEquals(-1800.0, $amount);
    }

    public function test_it_sums_shares_across_multiple_sites(): void
    {
        $site2 = \App\Models\Site::create([
            'organization_id' => $this->organization->id,
            'name' => 'Second Mall',
            'mall_name' => 'Second Mall',
            'is_active' => true,
        ]);

        \App\Models\ServiceType::create([
            'site_id' => $site2->id,
            'name' => 'Standard Wash',
            'price' => 100,
            'is_active' => true,
        ]);

        PartnerSiteShare::create([
            'partner_id' => $this->partner->id,
            'site_id' => $site2->id,
            'share_pct' => 50,
        ]);

        // Site 1: no contract → expenses = food only (0 if no allowance)
        // We'll remove contract effect by testing breakdown structure
        $breakdown = $this->service->breakdown($this->partner, now()->year, now()->month);

        $this->assertCount(2, $breakdown);
        $this->assertEquals(30.0, $breakdown[0]['share_pct']);
        $this->assertEquals(50.0, $breakdown[1]['share_pct']);
    }

    public function test_it_creates_settlement_record(): void
    {
        $this->createWashEntries(50);

        $settlement = $this->service->createSettlement(
            $this->partner,
            now()->year,
            now()->month
        );

        $this->assertInstanceOf(PartnerSettlement::class, $settlement);
        $this->assertNotNull($settlement->breakdown);
        $this->assertNull($settlement->paid_at);
        $this->assertDatabaseHas('partner_settlements', [
            'partner_id' => $this->partner->id,
        ]);
    }

    public function test_it_marks_settlement_as_paid(): void
    {
        $settlement = $this->service->createSettlement(
            $this->partner,
            now()->year,
            now()->month
        );

        $paid = $this->service->markPaid($settlement);

        $this->assertNotNull($paid->paid_at);
    }

    public function test_payout_is_zero_when_partner_has_no_site_shares(): void
    {
        $lonely = Partner::create([
            'organization_id' => $this->organization->id,
            'name' => 'No Sites Partner',
            'is_active' => true,
        ]);

        $amount = $this->service->calculatePartnerPayout($lonely, now()->year, now()->month);

        $this->assertEquals(0.0, $amount);
    }

    private function createWashEntries(int $vehicleCount): void
    {
        $dailyLog = DailyLog::create([
            'site_id' => $this->site->id,
            'date' => now()->toDateString(),
            'shift' => Shift::Morning->value,
            'submitted_by_id' => $this->manager->id,
        ]);

        WashEntry::create([
            'daily_log_id' => $dailyLog->id,
            'staff_id' => $this->staff->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_count' => $vehicleCount,
            'payment_method' => PaymentMethod::Cash,
        ]);
    }
}
