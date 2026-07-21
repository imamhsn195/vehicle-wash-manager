<?php

namespace App\Services;

use App\Enums\Shift;
use App\Exceptions\NoActiveServiceTypeException;
use App\Models\DailyLog;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WashEntry;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;

class DailyLogService
{
    public function recordEntry(array $data, User $submitter): WashEntry
    {
        $serviceType = null;

        if (! empty($data['service_type_id'])) {
            $serviceType = ServiceType::query()
                ->where('id', $data['service_type_id'])
                ->where('site_id', $data['site_id'])
                ->where('is_active', true)
                ->first();
        }

        $serviceType ??= $this->findActiveServiceType($data['site_id']);

        if (! $serviceType) {
            throw new NoActiveServiceTypeException;
        }

        $shift = $data['shift'] instanceof Shift
            ? $data['shift']->value
            : $data['shift'];

        $date = Carbon::parse($data['date'])->toDateString();

        $dailyLog = $this->findOrCreateDailyLog(
            siteId: (int) $data['site_id'],
            date: $date,
            shift: $shift,
            submitterId: $submitter->id,
        );

        return WashEntry::create([
            'daily_log_id' => $dailyLog->id,
            'staff_id' => $data['staff_id'],
            'service_type_id' => $serviceType->id,
            'vehicle_count' => $data['vehicle_count'],
            'payment_method' => $data['payment_method'],
        ]);
    }

    public function findOrCreateDailyLog(int $siteId, string $date, string $shift, int $submitterId): DailyLog
    {
        $dailyLog = DailyLog::query()
            ->where('site_id', $siteId)
            ->where('shift', $shift)
            ->whereDate('date', $date)
            ->first();

        if ($dailyLog) {
            return $dailyLog;
        }

        try {
            return DailyLog::create([
                'site_id' => $siteId,
                'date' => $date,
                'shift' => $shift,
                'submitted_by_id' => $submitterId,
                'is_closed' => false,
            ]);
        } catch (UniqueConstraintViolationException) {
            return DailyLog::query()
                ->where('site_id', $siteId)
                ->where('shift', $shift)
                ->whereDate('date', $date)
                ->firstOrFail();
        }
    }

    public function findActiveServiceType(int $siteId): ?ServiceType
    {
        return ServiceType::query()
            ->where('site_id', $siteId)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }
}
