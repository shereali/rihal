<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Result extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'gpa' => 'decimal:2',
        'percentage' => 'decimal:2',
        'class_position' => 'integer',
        'merit_list_position' => 'integer',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'parent_notified_at' => 'datetime',
        'subject_results' => 'array',
        'grade_distribution' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'user_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ReportCardComment::class);
    }
}
