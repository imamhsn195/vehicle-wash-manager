<?php

namespace Tests\Feature;

use App\Filament\Pages\PartnerPayoutReport;
use App\Filament\Resources\PayrollRecordResource\Pages\ListPayrollRecords;
use App\Models\Partner;
use App\Models\PartnerSiteShare;
use App\Models\PayrollRecord;
use App\Services\PayrollService;
use Livewire\Livewire;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class PartnerAndPayrollFeatureTest extends TestCase
{
    use SetsUpWashBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
    }

    public function test_partner_payout_report_loads(): void
    {
        $partner = Partner::create([
            'organization_id' => $this->organization->id,
            'name' => 'Feature Partner',
            'is_active' => true,
        ]);

        PartnerSiteShare::create([
            'partner_id' => $partner->id,
            'site_id' => $this->site->id,
            'share_pct' => 25,
        ]);

        Livewire::actingAs($this->admin)
            ->test(PartnerPayoutReport::class)
            ->assertOk()
            ->assertSee('Feature Partner');
    }

    public function test_payroll_generate_action_creates_records(): void
    {
        $this->staff->update([
            'salary_type' => 'monthly',
            'base_salary' => 12000,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ListPayrollRecords::class)
            ->callAction('generate', data: [
                'site_id' => $this->site->id,
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
            ])
            ->assertNotified();

        $this->assertEquals(1, PayrollRecord::count());
        $this->assertEquals(12000.0, (float) PayrollRecord::first()->net_amount);
    }

    public function test_mark_payroll_paid_via_table_action(): void
    {
        $this->staff->update(['salary_type' => 'monthly', 'base_salary' => 10000]);

        $record = app(PayrollService::class)->calculateForStaff(
            $this->staff,
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        Livewire::actingAs($this->admin)
            ->test(ListPayrollRecords::class)
            ->callTableAction('markPaid', $record);

        $this->assertNotNull($record->fresh()->paid_at);
    }
}
