<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orphan extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'birth_date' => 'date',
        'monthly_amount' => 'decimal:2',
        'total_sponsored' => 'decimal:2',
        'sponsorship_start_date' => 'date',
        'sponsorship_end_date' => 'date',
        'is_active' => 'boolean',
        'is_orphaned' => 'boolean',
        'is_needy' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Donor::class, 'sponsor_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SponsorshipPayment::class);
    }
}
