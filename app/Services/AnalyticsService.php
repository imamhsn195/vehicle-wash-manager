<?php

namespace App\Services;

use App\Models\WashEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalyticsService
{
    public function carsToday(?int $siteId = null): int
    {
        return $this->washEntriesQuery(today(), $siteId)->sum('vehicle_count');
    }

    public function revenueToday(?int $siteId = null): float
    {
        return (float) $this->washEntriesQuery(today(), $siteId)
            ->join('service_types', 'wash_entries.service_type_id', '=', 'service_types.id')
            ->selectRaw('SUM(wash_entries.vehicle_count * COALESCE(wash_entries.amount, service_types.price)) as total')
            ->value('total');
    }

    public function revenueBySiteToday(): Collection
    {
        return $this->revenueBySiteOnDate(today());
    }

    public function revenueBySiteOnDate(Carbon $date): Collection
    {
        return $this->washEntriesQuery($date)
            ->join('daily_logs', 'wash_entries.daily_log_id', '=', 'daily_logs.id')
            ->join('sites', 'daily_logs.site_id', '=', 'sites.id')
            ->join('service_types', 'wash_entries.service_type_id', '=', 'service_types.id')
            ->groupBy('sites.id', 'sites.name')
            ->selectRaw('sites.name as site_name, SUM(wash_entries.vehicle_count) as cars, SUM(wash_entries.vehicle_count * COALESCE(wash_entries.amount, service_types.price)) as revenue')
            ->get();
    }

    public function staffProductivityToday(): Collection
    {
        return $this->washEntriesQuery(today())
            ->join('staff', 'wash_entries.staff_id', '=', 'staff.id')
            ->groupBy('staff.id', 'staff.name')
            ->selectRaw('staff.name, SUM(wash_entries.vehicle_count) as cars')
            ->orderByDesc('cars')
            ->limit(10)
            ->get();
    }

    protected function washEntriesQuery(Carbon $date, ?int $siteId = null)
    {
        return WashEntry::query()
            ->whereHas('dailyLog', function ($query) use ($date, $siteId) {
                $query->whereDate('date', $date);
                if ($siteId) {
                    $query->where('site_id', $siteId);
                }
            });
    }
}
