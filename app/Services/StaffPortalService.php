<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\User;
use App\Models\WashEntry;
use Carbon\Carbon;

class StaffPortalService
{
    /**
     * @return array{
     *   cars_today: int,
     *   cars_yesterday: int,
     *   cars_this_month: int,
     *   estimated_earnings_today: float,
     *   estimated_earnings_month: float,
     *   salary_type: string,
     *   site_name: ?string
     * }
     */
    public function statsFor(Staff $staff): array
    {
        $carsToday = $this->carsOnDate($staff->id, today());
        $carsYesterday = $this->carsOnDate($staff->id, today()->subDay());
        $carsMonth = $this->carsInRange(
            $staff->id,
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        return [
            'cars_today' => $carsToday,
            'cars_yesterday' => $carsYesterday,
            'cars_this_month' => $carsMonth,
            'estimated_earnings_today' => $this->estimateEarnings($staff, $carsToday, daysWorked: $carsToday > 0 ? 1 : 0),
            'estimated_earnings_month' => $this->estimateEarnings(
                $staff,
                $carsMonth,
                daysWorked: $this->daysWorkedInRange($staff->id, now()->startOfMonth(), now()->endOfMonth())
            ),
            'salary_type' => $staff->salary_type,
            'site_name' => $staff->primarySite()?->name,
        ];
    }

    public function staffForUser(User $user): ?Staff
    {
        return Staff::query()->where('user_id', $user->id)->first();
    }

    protected function carsOnDate(int $staffId, Carbon $date): int
    {
        return (int) WashEntry::query()
            ->where('staff_id', $staffId)
            ->whereHas('dailyLog', fn ($q) => $q->whereDate('date', $date->toDateString()))
            ->sum('vehicle_count');
    }

    protected function carsInRange(int $staffId, Carbon $from, Carbon $to): int
    {
        return (int) WashEntry::query()
            ->where('staff_id', $staffId)
            ->whereHas('dailyLog', function ($q) use ($from, $to) {
                $q->whereDate('date', '>=', $from->toDateString())
                    ->whereDate('date', '<=', $to->toDateString());
            })
            ->sum('vehicle_count');
    }

    protected function daysWorkedInRange(int $staffId, Carbon $from, Carbon $to): int
    {
        return (int) WashEntry::query()
            ->where('staff_id', $staffId)
            ->whereHas('dailyLog', function ($q) use ($from, $to) {
                $q->whereDate('date', '>=', $from->toDateString())
                    ->whereDate('date', '<=', $to->toDateString());
            })
            ->join('daily_logs', 'wash_entries.daily_log_id', '=', 'daily_logs.id')
            ->distinct()
            ->count('daily_logs.date');
    }

    protected function estimateEarnings(Staff $staff, int $cars, int $daysWorked): float
    {
        return match ($staff->salary_type) {
            'daily' => (float) $staff->base_salary * $daysWorked,
            'monthly' => (float) $staff->base_salary,
            'per_car' => (float) $staff->per_wash_rate * $cars,
            'hybrid' => (float) $staff->base_salary + ((float) $staff->per_wash_rate * $cars),
            default => 0.0,
        };
    }
}
