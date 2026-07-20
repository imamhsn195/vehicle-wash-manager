<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAssignment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'staff_id',
        'site_id',
        'is_primary',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
