<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\DailyLog;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ContractRenewalService
{
    public function upcomingRenewals(?Carbon $asOf = null): Collection
    {
        $asOf ??= now();

        return Contract::query()
            ->with('site')
            ->whereIn('status', ['active', 'pending_renewal'])
            ->get()
            ->map(function (Contract $contract) use ($asOf) {
                $daysUntil = (int) $asOf->copy()->startOfDay()->diffInDays($contract->end_date->startOfDay(), false);
                $contract->days_until_expiry = $daysUntil;
                $contract->is_overdue = $daysUntil < 0;

                return $contract;
            })
            ->filter(function (Contract $contract) {
                return $contract->days_until_expiry <= $contract->renewal_reminder_days;
            })
            ->sortBy('days_until_expiry')
            ->values();
    }

    public function flagPendingRenewals(?Carbon $asOf = null): int
    {
        $asOf ??= now();
        $ids = $this->upcomingRenewals($asOf)
            ->where('status', 'active')
            ->pluck('id');

        return Contract::query()
            ->whereIn('id', $ids)
            ->update(['status' => 'pending_renewal']);
    }

    public function sitesMissingTodayLog(?Carbon $date = null): Collection
    {
        $date ??= now();

        return Site::query()
            ->where('is_active', true)
            ->whereDoesntHave('dailyLogs', function ($query) use ($date) {
                $query->whereDate('date', $date->toDateString());
            })
            ->get();
    }
}
