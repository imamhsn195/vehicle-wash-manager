<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerSiteShare extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'partner_id',
        'site_id',
        'share_pct',
    ];

    protected function casts(): array
    {
        return [
            'share_pct' => 'decimal:2',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
