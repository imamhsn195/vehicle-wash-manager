<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Enums\UserRole;
use App\Mail\DailySummaryMail;
use App\Models\DailyLog;
use App\Models\User;
use App\Models\WashEntry;
use App\Services\AnalyticsService;
use App\Services\DailySummaryService;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class DailySummaryServiceTest extends TestCase
{
    use SetsUpWashBusiness;

    private DailySummaryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
        $this->service = new DailySummaryService(new AnalyticsService);
    }

    public function test_it_builds_summary_payload(): void
    {
        $this->createWashEntry(12);

        $summary = $this->service->build(today());

        $this->assertEquals(12, $summary['total_cars']);
        $this->assertEquals(2400.0, $summary['total_revenue']);
        $this->assertNotEmpty($summary['by_site']);
        $this->assertEquals(today()->toDateString(), $summary['date']);
    }

    public function test_it_sends_email_to_admins(): void
    {
        Mail::fake();

        User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Admin,
            'email' => 'owner2@test.test',
        ]);

        $this->createWashEntry(5);

        $sent = $this->service->sendToAdmins($this->organization, today());

        $this->assertGreaterThanOrEqual(1, $sent);
        Mail::assertSent(DailySummaryMail::class);
    }

    public function test_it_returns_zero_when_no_admins(): void
    {
        Mail::fake();

        $org = \App\Models\Organization::create(['name' => 'Empty Org']);

        $sent = $this->service->sendToAdmins($org, today());

        $this->assertEquals(0, $sent);
        Mail::assertNothingSent();
    }

    private function createWashEntry(int $vehicleCount): void
    {
        $dailyLog = DailyLog::create([
            'site_id' => $this->site->id,
            'date' => today()->toDateString(),
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
