<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'serial_number', 'device_type', 'ip_address',
        'port', 'api_key', 'status', 'location', 'tenant_id',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
