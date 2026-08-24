<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuedCertificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'template_id', 'student_id', 'class_id',
        'subject_id', 'certificate_number',
        'issue_date', 'authorized_by', 'remarks',
        'tenant_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function templateRelation(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    public function studentRelation(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classRelation(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function subjectRelation(): BelongsTo
    {
        return $this->belongsTo(AcademicSubject::class, 'subject_id');
    }
}
