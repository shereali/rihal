<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardingMeal extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'date',
        'breakfast',
        'lunch',
        'dinner',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
