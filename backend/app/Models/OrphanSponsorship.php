<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrphanSponsorship extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'monthly_commitment' => 'decimal:2',
        'share_percent' => 'decimal:2',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function orphan(): BelongsTo
    {
        return $this->belongsTo(Orphan::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }
}
