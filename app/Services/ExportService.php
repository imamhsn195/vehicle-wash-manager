<?php

namespace App\Services;

use App\Exports\DailyLogsExport;
use App\Exports\ExpensesExport;
use App\Exports\SitePnLExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportService
{
    public function __construct(
        protected PnLService $pnlService
    ) {}

    public function downloadDailyLogs(Carbon $from, Carbon $to): BinaryFileResponse
    {
        return Excel::download(new DailyLogsExport($from, $to), 'daily-logs.xlsx');
    }

    public function downloadExpenses(Carbon $from, Carbon $to): BinaryFileResponse
    {
        return Excel::download(new ExpensesExport($from, $to), 'expenses.xlsx');
    }

    public function downloadSitePnL(int $year, int $month): BinaryFileResponse
    {
        return Excel::download(
            new SitePnLExport($this->pnlService, $year, $month),
            'site-pnl.xlsx'
        );
    }
}
