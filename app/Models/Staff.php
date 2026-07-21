<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [
        'organization_id',
        'user_id',
        'employee_code',
        'name',
        'phone',
        'staff_type',
        'salary_type',
        'base_salary',
        'per_wash_rate',
        'has_housing',
        'daily_food_allowance',
        'hire_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'per_wash_rate' => 'decimal:2',
            'daily_food_allowance' => 'decimal:2',
            'has_housing' => 'boolean',
            'is_active' => 'boolean',
            'hire_date' => 'date',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class);
    }

    public function washEntries(): HasMany
    {
        return $this->hasMany(WashEntry::class);
    }

    public function primarySite(): ?Site
    {
        return $this->assignments()->where('is_primary', true)->first()?->site;
    }
}
