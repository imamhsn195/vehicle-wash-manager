<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Exports\DailyLogsExport;
use App\Exports\ExpensesExport;
use App\Exports\SitePnLExport;
use App\Models\DailyLog;
use App\Models\WashEntry;
use App\Services\ExpenseService;
use App\Services\ExportService;
use App\Services\PnLService;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class ExportServiceTest extends TestCase
{
    use SetsUpWashBusiness;

    private ExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
        $this->service = new ExportService(new PnLService(new ExpenseService));
    }

    public function test_daily_logs_export_downloadable(): void
    {
        Excel::fake();

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
            'vehicle_count' => 5,
            'payment_method' => PaymentMethod::Cash,
        ]);

        $this->service->downloadDailyLogs(now()->startOfMonth(), now()->endOfMonth());

        Excel::assertDownloaded('daily-logs.xlsx', function (DailyLogsExport $export) {
            return $export->collection()->count() === 1;
        });
    }

    public function test_expenses_export_downloadable(): void
    {
        Excel::fake();

        $this->service->downloadExpenses(now()->startOfMonth(), now()->endOfMonth());

        Excel::assertDownloaded('expenses.xlsx');
    }

    public function test_pnl_export_downloadable(): void
    {
        Excel::fake();

        $this->service->downloadSitePnL(now()->year, now()->month);

        Excel::assertDownloaded('site-pnl.xlsx', function (SitePnLExport $export) {
            return $export->collection()->isNotEmpty();
        });
    }
}
