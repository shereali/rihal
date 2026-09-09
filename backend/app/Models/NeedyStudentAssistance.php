<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NeedyStudentAssistance extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'student_name',
        'class_name',
        'support_type',
        'amount_discount',
        'fund_source',
        'status',
    ];
}
