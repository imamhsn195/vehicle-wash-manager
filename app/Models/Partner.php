<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'name',
        'email',
        'phone',
        'global_share_pct',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'global_share_pct' => 'decimal:2',
            'is_active' => 'boolean',
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

    public function siteShares(): HasMany
    {
        return $this->hasMany(PartnerSiteShare::class);
    }
}
