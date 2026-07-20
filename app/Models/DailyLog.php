<?php

namespace App\Models;

use App\Enums\Shift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyLog extends Model
{
    protected $fillable = [
        'site_id',
        'date',
        'shift',
        'submitted_by_id',
        'notes',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'shift' => Shift::class,
            'is_closed' => 'boolean',
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

    public function entries(): HasMany
    {
        return $this->hasMany(WashEntry::class);
    }

    public function totalCars(): int
    {
        return (int) $this->entries()->sum('vehicle_count');
    }

    public function totalRevenue(): float
    {
        return $this->entries()
            ->with('serviceType')
            ->get()
            ->sum(fn (WashEntry $entry) => $entry->vehicle_count * (float) ($entry->amount ?? $entry->serviceType?->price ?? 0));
    }
}
