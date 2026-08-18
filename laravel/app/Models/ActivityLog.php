<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'activity_at' => 'datetime',
        'ip_address' => 'string',
        'user_agent' => 'string',
        'before_data' => 'array',
        'after_data' => 'array',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function entity(): BelongsTo
    {
        return $this->morphTo();
    }
}
