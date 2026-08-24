<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'template_type', 'template_data',
        'class_id', 'subject_id', 'tenant_id',
        'is_active', 'issued_by',
    ];

    protected $casts = [
        'template_data' => 'array',
        'is_active'     => 'boolean',
    ];

    public function classRelation(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function subjectRelation(): BelongsTo
    {
        return $this->belongsTo(AcademicSubject::class, 'subject_id');
    }
}
