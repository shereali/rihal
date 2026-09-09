<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostelVisitor extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'visit_time' => 'datetime',
        'departure_time' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'room_id');
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_id');
    }
}
