<?php

namespace App\Exports;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExpensesExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected Carbon $from,
        protected Carbon $to
    ) {}

    public function collection(): Collection
    {
        return Expense::query()
            ->with('site')
            ->whereDate('date', '>=', $this->from->toDateString())
            ->whereDate('date', '<=', $this->to->toDateString())
            ->orderBy('date')
            ->get()
            ->map(fn (Expense $expense) => [
                $expense->date->toDateString(),
                $expense->site?->name ?? 'Org-wide',
                $expense->type->value,
                $expense->category->value,
                $expense->description,
                (float) $expense->amount,
                $expense->status->value,
            ]);
    }

    public function headings(): array
    {
        return ['Date', 'Site', 'Type', 'Category', 'Description', 'Amount', 'Status'];
    }
}
