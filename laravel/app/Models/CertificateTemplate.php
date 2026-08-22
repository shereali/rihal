<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'is_active' => 'boolean',
        'fields' => 'array',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function issuedCertificates(): HasMany { return $this->hasMany(IssuedCertificate::class); }
}
