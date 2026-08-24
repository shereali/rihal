<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id', 'from_class_id', 'to_class_id',
        'academic_year', 'promotion_date', 'status',
        'comments', 'promoted_by', 'tenant_id',
    ];

    protected $casts = [
        'promotion_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'from_class_id');
    }

    public function toClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'to_class_id');
    }
}
