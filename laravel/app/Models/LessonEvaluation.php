<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonEvaluation extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'class_id',
        'subject_id',
        'book_id',
        'date',
        'day',
        'month',
        'year',
        'grade',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function academicSubject()
    {
        return $this->belongsTo(AcademicSubject::class, 'subject_id');
    }
}
