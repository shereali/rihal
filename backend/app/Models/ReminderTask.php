<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderTask extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
        'delivery_channels' => 'array',
        'template_id' => 'integer',
        'target_entities' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
