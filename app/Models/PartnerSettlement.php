<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerSettlement extends Model
{
    protected $fillable = [
        'partner_id',
        'period_start',
        'period_end',
        'amount',
        'breakdown',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'amount' => 'decimal:2',
            'breakdown' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }
}
