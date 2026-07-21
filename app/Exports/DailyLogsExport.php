<?php

namespace App\Exports;

use App\Models\WashEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailyLogsExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected Carbon $from,
        protected Carbon $to
    ) {}

    public function collection(): Collection
    {
        return WashEntry::query()
            ->with(['dailyLog.site', 'staff', 'serviceType'])
            ->whereHas('dailyLog', function ($query) {
                $query->whereDate('date', '>=', $this->from->toDateString())
                    ->whereDate('date', '<=', $this->to->toDateString());
            })
            ->get()
            ->map(fn (WashEntry $entry) => [
                $entry->dailyLog->date->toDateString(),
                $entry->dailyLog->site->name,
                $entry->dailyLog->shift->value,
                $entry->staff->name,
                $entry->vehicle_count,
                $entry->payment_method->value,
                $entry->revenue(),
            ]);
    }

    public function headings(): array
    {
        return ['Date', 'Site', 'Shift', 'Staff', 'Cars', 'Payment', 'Revenue'];
    }
}
