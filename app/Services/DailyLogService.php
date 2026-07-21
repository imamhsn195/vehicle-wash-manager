<?php

namespace App\Services;

use App\Enums\Shift;
use App\Exceptions\NoActiveServiceTypeException;
use App\Models\DailyLog;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WashEntry;
use Illuminate\Support\Carbon;

class DailyLogService
{
    public function recordEntry(array $data, User $submitter): WashEntry
    {
        $serviceType = $this->findActiveServiceType($data['site_id']);

        if (! $serviceType) {
            throw new NoActiveServiceTypeException;
        }

        $shift = $data['shift'] instanceof Shift
            ? $data['shift']->value
            : $data['shift'];

        $date = Carbon::parse($data['date'])->toDateString();

        $dailyLog = DailyLog::query()
            ->where('site_id', $data['site_id'])
            ->where('shift', $shift)
            ->whereDate('date', $date)
            ->first();

        if (! $dailyLog) {
            $dailyLog = DailyLog::create([
                'site_id' => $data['site_id'],
                'date' => $date,
                'shift' => $shift,
                'submitted_by_id' => $submitter->id,
                'is_closed' => false,
            ]);
        }

        return WashEntry::create([
            'daily_log_id' => $dailyLog->id,
            'staff_id' => $data['staff_id'],
            'service_type_id' => $serviceType->id,
            'vehicle_count' => $data['vehicle_count'],
            'payment_method' => $data['payment_method'],
        ]);
    }

    public function findActiveServiceType(int $siteId): ?ServiceType
    {
        return ServiceType::query()
            ->where('site_id', $siteId)
            ->where('is_active', true)
            ->first();
    }
}
