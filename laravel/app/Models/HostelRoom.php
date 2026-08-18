<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostelRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'floor' => 'integer',
        'capacity' => 'integer',
        'current_occupancy' => 'integer',
        'monthly_rent' => 'decimal:2',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'amenities' => 'array',
        'students' => 'array',
        'images' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function warden(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warden_id');
    }

    public function visitors(): HasMany
    {
        return $this->hasMany(HostelVisitor::class);
    }
}
