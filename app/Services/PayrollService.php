<?php

namespace App\Services;

use App\Models\PayrollRecord;
use App\Models\Site;
use App\Models\Staff;
use App\Models\WashEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PayrollService
{
    public function calculateForStaff(Staff $staff, Carbon $periodStart, Carbon $periodEnd): PayrollRecord
    {
        $cars = $this->carsWashed($staff->id, $periodStart, $periodEnd);
        $daysWorked = $this->daysWorked($staff->id, $periodStart, $periodEnd);

        [$base, $bonus] = match ($staff->salary_type) {
            'daily' => [(float) $staff->base_salary * $daysWorked, 0.0],
            'monthly' => [(float) $staff->base_salary, 0.0],
            'per_car' => [0.0, (float) $staff->per_wash_rate * $cars],
            'hybrid' => [(float) $staff->base_salary, (float) $staff->per_wash_rate * $cars],
            default => [0.0, 0.0],
        };

        $deductions = 0.0;
        $net = $base + $bonus - $deductions;

        return PayrollRecord::updateOrCreate(
            [
                'staff_id' => $staff->id,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ],
            [
                'base_amount' => $base,
                'wash_bonus' => $bonus,
                'deductions' => $deductions,
                'net_amount' => $net,
                'cars_washed' => $cars,
                'days_worked' => $daysWorked,
                'salary_type' => $staff->salary_type,
            ]
        );
    }

    public function generateForSite(Site $site, Carbon $periodStart, Carbon $periodEnd): Collection
    {
        return Staff::query()
            ->where('is_active', true)
            ->whereHas('assignments', fn ($q) => $q->where('site_id', $site->id)->where('is_primary', true))
            ->get()
            ->map(fn (Staff $staff) => $this->calculateForStaff($staff, $periodStart, $periodEnd));
    }

    public function markPaid(PayrollRecord $record): PayrollRecord
    {
        $record->update(['paid_at' => now()]);

        return $record->fresh();
    }

    protected function carsWashed(int $staffId, Carbon $periodStart, Carbon $periodEnd): int
    {
        return (int) WashEntry::query()
            ->where('staff_id', $staffId)
            ->whereHas('dailyLog', function ($query) use ($periodStart, $periodEnd) {
                $query->whereDate('date', '>=', $periodStart->toDateString())
                    ->whereDate('date', '<=', $periodEnd->toDateString());
            })
            ->sum('vehicle_count');
    }

    protected function daysWorked(int $staffId, Carbon $periodStart, Carbon $periodEnd): int
    {
        return (int) WashEntry::query()
            ->where('staff_id', $staffId)
            ->whereHas('dailyLog', function ($query) use ($periodStart, $periodEnd) {
                $query->whereDate('date', '>=', $periodStart->toDateString())
                    ->whereDate('date', '<=', $periodEnd->toDateString());
            })
            ->join('daily_logs', 'wash_entries.daily_log_id', '=', 'daily_logs.id')
            ->distinct()
            ->count('daily_logs.date');
    }
}
