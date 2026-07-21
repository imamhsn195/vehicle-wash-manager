<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Site;
use App\Models\Staff;
use App\Models\WashEntry;
use Carbon\Carbon;

class PnLService
{
    public function __construct(
        protected ExpenseService $expenseService
    ) {}

    /**
     * @return array{
     *   revenue: float,
     *   cars: int,
     *   expenses: float,
     *   mall_contract_allocated: float,
     *   staff_food_cost: float,
     *   recorded_expenses: float,
     *   profit: float,
     *   margin_pct: float,
     *   cost_per_wash: float
     * }
     */
    public function siteMonthlyPnL(Site $site, int $year, int $month): array
    {
        $cars = $this->carsForSiteMonth($site->id, $year, $month);
        $revenue = $this->revenueForSiteMonth($site->id, $year, $month);
        $mallAllocated = $this->mallContractAllocated($site, $year, $month);
        $staffFood = $this->staffFoodCostForSite($site, $year, $month);
        $recorded = $this->expenseService->approvedTotalForSite($site->id, $year, $month);
        $expenses = $mallAllocated + $staffFood + $recorded;
        $profit = $revenue - $expenses;

        return [
            'revenue' => $revenue,
            'cars' => $cars,
            'expenses' => $expenses,
            'mall_contract_allocated' => $mallAllocated,
            'staff_food_cost' => $staffFood,
            'recorded_expenses' => $recorded,
            'profit' => $profit,
            'margin_pct' => $revenue > 0 ? ($profit / $revenue) * 100 : 0.0,
            'cost_per_wash' => $cars > 0 ? $expenses / $cars : 0.0,
        ];
    }

    public function mallContractAllocated(Site $site, int $year, int $month): float
    {
        $contract = Contract::query()
            ->where('site_id', $site->id)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', Carbon::create($year, $month)->endOfMonth())
            ->whereDate('end_date', '>=', Carbon::create($year, $month)->startOfMonth())
            ->latest('end_date')
            ->first();

        if (! $contract) {
            return 0.0;
        }

        return (float) $contract->annual_value / 12;
    }

    public function staffFoodCostForSite(Site $site, int $year, int $month): float
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        $dailyTotal = (float) Staff::query()
            ->whereHas('assignments', fn ($q) => $q->where('site_id', $site->id)->where('is_primary', true))
            ->where('is_active', true)
            ->sum('daily_food_allowance');

        return $dailyTotal * $daysInMonth;
    }

    public function revenueForSiteMonth(int $siteId, int $year, int $month): float
    {
        return (float) WashEntry::query()
            ->whereHas('dailyLog', function ($query) use ($siteId, $year, $month) {
                $query->where('site_id', $siteId)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month);
            })
            ->join('service_types', 'wash_entries.service_type_id', '=', 'service_types.id')
            ->selectRaw('SUM(wash_entries.vehicle_count * COALESCE(wash_entries.amount, service_types.price)) as total')
            ->value('total');
    }

    public function carsForSiteMonth(int $siteId, int $year, int $month): int
    {
        return (int) WashEntry::query()
            ->whereHas('dailyLog', function ($query) use ($siteId, $year, $month) {
                $query->where('site_id', $siteId)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month);
            })
            ->sum('vehicle_count');
    }
}
