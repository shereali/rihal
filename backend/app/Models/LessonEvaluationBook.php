<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonEvaluationBook extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'subject_id',
        'name',
    ];
}
