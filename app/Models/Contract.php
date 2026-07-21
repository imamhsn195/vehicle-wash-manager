<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $fillable = [
        'site_id',
        'title',
        'annual_value',
        'start_date',
        'end_date',
        'status',
        'terms',
        'renewal_reminder_days',
    ];

    protected function casts(): array
    {
        return [
            'annual_value' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
