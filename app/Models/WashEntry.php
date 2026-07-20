<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WashEntry extends Model
{
    protected $fillable = [
        'daily_log_id',
        'staff_id',
        'service_type_id',
        'vehicle_count',
        'payment_method',
        'amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'amount' => 'decimal:2',
        ];
    }

    public function dailyLog(): BelongsTo
    {
        return $this->belongsTo(DailyLog::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function revenue(): float
    {
        return $this->vehicle_count * (float) ($this->amount ?? $this->serviceType?->price ?? 0);
    }
}
