<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donor extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'is_verified' => 'boolean',
        'is_recurring' => 'boolean',
        'preferred_language' => 'string',
        'preferred_channel' => 'string',
        'total_donated_amount' => 'decimal:2',
        'last_donation_date' => 'date',
        'lifetime_donation_value' => 'decimal:2',
        'rating_score' => 'decimal:2',
        'communication_log' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }
}
