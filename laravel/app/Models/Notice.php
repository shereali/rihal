<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notice extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'expired_at' => 'datetime',
        'is_pinned' => 'boolean',
        'is_scheduled' => 'boolean',
        'is_active' => 'boolean',
        'target_audience' => 'array',
        'channels' => 'array',
        'attachments' => 'array',
        'read_by_count' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function readReceipts(): HasMany
    {
        return $this->hasMany(NoticeReadReceipt::class);
    }
}
