<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'tag',
        'name',
        'category',
        'purchase_date',
        'location',
        'cost',
        'dep_rate',
        'book_value',
    ];
}
