<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDocument extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'file_size' => 'integer',
        'document_type' => 'string',
        'file_path' => 'string',
        'file_url' => 'string',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'verified_by' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
