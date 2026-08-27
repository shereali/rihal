<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffDischarge extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'staff_id',
        'name',
        'designation',
        'department',
        'joining_date',
        'discharge_date',
        'reason',
        'status',
    ];
}
