<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyAlertAcknowledgement extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'acknowledged_at' => 'datetime',
        'acknowledged_through' => 'string',
    ];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(EmergencyAlert::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
