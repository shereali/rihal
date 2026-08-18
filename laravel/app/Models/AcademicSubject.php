<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSubject extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'is_active' => 'boolean',
        'teaching_hours_per_week' => 'integer',
        'credit_hours' => 'decimal:1',
        'is_core' => 'boolean',
        'is_optional' => 'boolean',
        'exam_weightage' => 'array',
    ];

    public function classes(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function sessions(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }
}
