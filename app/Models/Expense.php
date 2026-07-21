<?php

namespace App\Models;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'organization_id',
        'site_id',
        'type',
        'category',
        'description',
        'amount',
        'date',
        'status',
        'submitted_by_id',
        'approved_by_id',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ExpenseType::class,
            'category' => ExpenseCategory::class,
            'status' => ExpenseStatus::class,
            'amount' => 'decimal:2',
            'date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function isPending(): bool
    {
        return $this->status === ExpenseStatus::Pending;
    }
}
