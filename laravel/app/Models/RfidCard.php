<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfidCard extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'card_uid',
        'user_id',
        'holder_name',
        'role',
        'designation',
        'class_name',
        'issue_date',
        'status',
    ];
}
