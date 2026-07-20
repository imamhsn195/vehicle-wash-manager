<?php

namespace App\Services;

use App\Models\Site;
use Carbon\Carbon;

class BreakEvenService
{
    public function __construct(
        protected PnLService $pnlService
    ) {}

    /**
     * @return array{
     *   avg_price: float,
     *   variable_cost_per_wash: float,
     *   daily_fixed_cost: float,
     *   contribution: float,
     *   break_even_cars: ?int,
     *   is_finite: bool,
     *   avg_daily_cars: float,
     *   is_below_break_even: bool
     * }
     */
    public function forSite(Site $site, int $year, int $month): array
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $pnl = $this->pnlService->siteMonthlyPnL($site, $year, $month);

        $avgPrice = (float) ($site->primaryServiceType?->price
            ?? $site->serviceTypes()->where('is_active', true)->value('price')
            ?? 0);

        $mallDaily = $this->pnlService->mallContractAllocated($site, $year, $month) / $daysInMonth;
        $foodDaily = $this->pnlService->staffFoodCostForSite($site, $year, $month) / $daysInMonth;
        $recordedDaily = $pnl['recorded_expenses'] / $daysInMonth;

        // Treat recorded expenses as variable; mall + food as fixed for break-even
        $dailyFixed = $mallDaily + $foodDaily;
        $variablePerWash = $pnl['cars'] > 0
            ? $pnl['recorded_expenses'] / $pnl['cars']
            : 0.0;

        $contribution = $avgPrice - $variablePerWash;
        $isFinite = $contribution > 0;
        $breakEven = $isFinite ? (int) ceil($dailyFixed / $contribution) : null;

        $avgDailyCars = $daysInMonth > 0 ? $pnl['cars'] / $daysInMonth : 0.0;
        $isBelow = $isFinite && $avgDailyCars < $breakEven;

        return [
            'avg_price' => $avgPrice,
            'variable_cost_per_wash' => $variablePerWash,
            'daily_fixed_cost' => $dailyFixed,
            'contribution' => $contribution,
            'break_even_cars' => $breakEven,
            'is_finite' => $isFinite,
            'avg_daily_cars' => $avgDailyCars,
            'is_below_break_even' => $isBelow,
        ];
    }

    /**
     * @return array<int, array{site: string, break_even_cars: ?int, avg_daily_cars: float, is_below_break_even: bool}>
     */
    public function allSites(int $year, int $month): array
    {
        return Site::query()
            ->where('is_active', true)
            ->get()
            ->map(function (Site $site) use ($year, $month) {
                $result = $this->forSite($site, $year, $month);

                return [
                    'site' => $site->name,
                    'site_id' => $site->id,
                    'break_even_cars' => $result['break_even_cars'],
                    'avg_daily_cars' => $result['avg_daily_cars'],
                    'is_below_break_even' => $result['is_below_break_even'],
                    'daily_fixed_cost' => $result['daily_fixed_cost'],
                    'avg_price' => $result['avg_price'],
                ];
            })
            ->all();
    }
}
