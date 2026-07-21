<?php

namespace App\Services;

use App\Models\CashReconciliation;
use App\Models\User;
use App\Models\WashEntry;
use Illuminate\Support\Carbon;

class CashReconciliationService
{
    public function expectedRevenue(int $siteId, string $date): float
    {
        return (float) WashEntry::query()
            ->whereHas('dailyLog', function ($query) use ($siteId, $date) {
                $query->where('site_id', $siteId)->whereDate('date', $date);
            })
            ->join('service_types', 'wash_entries.service_type_id', '=', 'service_types.id')
            ->selectRaw('SUM(wash_entries.vehicle_count * COALESCE(wash_entries.amount, service_types.price)) as total')
            ->value('total');
    }

    public function record(array $data, User $submitter): CashReconciliation
    {
        $date = Carbon::parse($data['date'])->toDateString();
        $expected = $this->expectedRevenue($data['site_id'], $date);

        $record = CashReconciliation::query()
            ->where('site_id', $data['site_id'])
            ->whereDate('date', $date)
            ->first();

        $payload = [
            'expected_revenue' => $expected,
            'cash_collected' => $data['cash_collected'],
            'deposited_amount' => $data['deposited_amount'] ?? $data['cash_collected'],
            'is_deposited' => $data['is_deposited'] ?? false,
            'submitted_by_id' => $submitter->id,
            'notes' => $data['notes'] ?? null,
        ];

        if ($record) {
            $record->update($payload);

            return $record->fresh();
        }

        return CashReconciliation::create([
            'site_id' => $data['site_id'],
            'date' => $date,
            ...$payload,
        ]);
    }
}
