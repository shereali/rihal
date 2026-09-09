<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'graduation_date' => 'date',
        'is_active' => 'boolean',
        'id_card_url' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(StudentGuardian::class, 'guardian_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function healthRecords(): HasMany
    {
        return $this->hasMany(StudentHealthRecord::class);
    }

    public function medicalHistory(): HasMany
    {
        return $this->hasMany(StudentMedicalHistory::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function markEntries(): HasMany
    {
        return $this->hasMany(MarkEntry::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function homeworkSubmissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    public function transportAssignments(): HasMany
    {
        return $this->hasMany(TransportAssignment::class);
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }
}
