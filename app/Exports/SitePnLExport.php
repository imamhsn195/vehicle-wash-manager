<?php

namespace App\Exports;

use App\Models\Site;
use App\Services\PnLService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SitePnLExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected PnLService $pnlService,
        protected int $year,
        protected int $month
    ) {}

    public function collection(): Collection
    {
        return Site::query()
            ->where('is_active', true)
            ->get()
            ->map(function (Site $site) {
                $pnl = $this->pnlService->siteMonthlyPnL($site, $this->year, $this->month);

                return [
                    $site->name,
                    $pnl['cars'],
                    $pnl['revenue'],
                    $pnl['expenses'],
                    $pnl['profit'],
                    round($pnl['margin_pct'], 2),
                    round($pnl['cost_per_wash'], 2),
                ];
            });
    }

    public function headings(): array
    {
        return ['Site', 'Cars', 'Revenue', 'Expenses', 'Profit', 'Margin %', 'Cost/Wash'];
    }
}
