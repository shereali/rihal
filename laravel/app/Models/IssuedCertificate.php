<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssuedCertificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'issued_at' => 'datetime',
        'is_delivered' => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function template(): BelongsTo { return $this->belongsTo(CertificateTemplate::class); }
    public function issuedBy(): BelongsTo { return $this->belongsTo(User::class, 'issued_by_user_id'); }
    public function marks(): HasMany { return $this->hasMany(CertificateMark::class, 'certificate_id'); }
}
