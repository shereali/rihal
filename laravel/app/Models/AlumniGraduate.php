<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniGraduate extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'sanad_no',
        'batch',
        'phone',
        'degree',
        'workplace',
        'designation',
        'status',
        'preferred_job',
        'preferred_location',
        'institution',
        'country',
        'joining_date',
    ];
}
