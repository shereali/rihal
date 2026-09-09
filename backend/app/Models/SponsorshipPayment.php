<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsorshipPayment extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function orphan(): BelongsTo
    {
        return $this->belongsTo(Orphan::class);
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Donor::class, 'sponsor_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by_user_id');
    }
}
