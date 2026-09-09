<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendancePattern extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'attendance_rate' => 'decimal:2',
        'risk_score' => 'decimal:2',
        'risk_factors' => 'array',
        'risk_level' => 'string',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
