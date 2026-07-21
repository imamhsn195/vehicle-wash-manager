<?php

namespace App\Services;

use App\Enums\ExpenseStatus;
use App\Exceptions\ExpenseAlreadyProcessedException;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Carbon;

class ExpenseService
{
    public function submit(array $data, User $submitter): Expense
    {
        return Expense::create([
            ...$data,
            'status' => ExpenseStatus::Pending,
            'submitted_by_id' => $submitter->id,
        ]);
    }

    public function approve(Expense $expense, User $approver): Expense
    {
        $this->ensurePending($expense);

        $expense->update([
            'status' => ExpenseStatus::Approved,
            'approved_by_id' => $approver->id,
            'approved_at' => now(),
        ]);

        return $expense->fresh();
    }

    public function reject(Expense $expense, User $approver): Expense
    {
        $this->ensurePending($expense);

        $expense->update([
            'status' => ExpenseStatus::Rejected,
            'approved_by_id' => $approver->id,
            'approved_at' => now(),
        ]);

        return $expense->fresh();
    }

    public function approvedTotalForSite(int $siteId, int $year, int $month): float
    {
        return (float) Expense::query()
            ->where('site_id', $siteId)
            ->where('status', ExpenseStatus::Approved)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');
    }

    protected function ensurePending(Expense $expense): void
    {
        if (! $expense->isPending()) {
            throw new ExpenseAlreadyProcessedException;
        }
    }
}
