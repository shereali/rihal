<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plugin extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'is_active' => 'boolean',
        'is_installed' => 'boolean',
        'is_enabled' => 'boolean',
        'installed_at' => 'datetime',
        'hooks' => 'array',
        'config' => 'array',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function installer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'installed_by_user_id');
    }
}
