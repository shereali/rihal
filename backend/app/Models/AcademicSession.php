<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSession extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'is_current' => 'boolean',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'string',
        'terms' => 'array',
    ];

    public function getSessionNameAttribute(): ?string
    {
        return $this->name_en ?: $this->name_bn;
    }

    public function getSessionBnAttribute(): ?string
    {
        return $this->name_bn;
    }

    public function setSessionNameAttribute($value): void
    {
        $this->attributes['name_en'] = $value;
        if (empty($this->attributes['name_bn'])) {
            $this->attributes['name_bn'] = $value;
        }
    }

    public function setSessionBnAttribute($value): void
    {
        $this->attributes['name_bn'] = $value;
    }

    public function classes(): HasMany
    {
        return $this->hasMany(AcademicClass::class, 'session_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
