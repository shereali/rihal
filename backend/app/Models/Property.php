<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'is_active' => 'boolean',
        'area_square_feet' => 'integer',
        'area_acres' => 'decimal:4',
        'purchase_date' => 'date',
        'registration_date' => 'date',
        'market_value' => 'decimal:2',
        'purchase_value' => 'decimal:2',
        'usage_type' => 'string',
        'documents' => 'array',
        'images' => 'array',
        'location_details' => 'array',
    ];



    public function documents(): HasMany
    {
        return $this->hasMany(PropertyDocument::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(PropertyMaintenance::class);
    }
}
