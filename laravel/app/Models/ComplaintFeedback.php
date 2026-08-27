<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintFeedback extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'complaint_feedbacks';

    protected $fillable = [
        'tenant_id',
        'tracking_id',
        'date',
        'sender_name',
        'sender_type',
        'category',
        'priority',
        'subject',
        'description',
        'status',
        'resolved_at',
    ];
}
