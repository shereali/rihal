<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'activated_at' => 'datetime',
        'modules_enabled' => 'array',
        'settings' => 'array',
    ];

    // Scope to get active tenants
    public function scopeActive($query)
    {
        return $query->where('subscription_status', 'active');
    }

    // Relationship to users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relationship to branches
    public function branches()
    {
        return $this->hasMany(TenantBranch::class);
    }
}
