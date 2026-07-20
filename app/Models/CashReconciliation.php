<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashReconciliation extends Model
{
    protected $fillable = [
        'site_id',
        'date',
        'expected_revenue',
        'cash_collected',
        'deposited_amount',
        'is_deposited',
        'submitted_by_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'expected_revenue' => 'decimal:2',
            'cash_collected' => 'decimal:2',
            'deposited_amount' => 'decimal:2',
            'is_deposited' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_id');
    }

    public function difference(): float
    {
        return (float) $this->cash_collected - (float) $this->expected_revenue;
    }

    public function hasDiscrepancy(): bool
    {
        return abs($this->difference()) > 0.01;
    }
}
