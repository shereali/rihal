<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateMark extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    public $timestamps = false;

    public function certificate(): BelongsTo { return $this->belongsTo(IssuedCertificate::class); }
    public function subject(): BelongsTo { return $this->belongsTo(AcademicSubject::class); }
}
