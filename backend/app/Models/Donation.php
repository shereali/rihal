<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'donation_date' => 'date',
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'is_anonymous' => 'boolean',
        'is_acknowledged' => 'boolean',
        'acknowledgment_sent_at' => 'datetime',
        'receipt_generated' => 'boolean',
        'receipt_url' => 'string',
        'payment_method' => 'string',
        'transaction_reference' => 'string',
        'reminder_sent_at' => 'datetime',
        'reminder_count' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }

    public function fund(): BelongsTo
    {
        return $this->belongsTo(Fund::class);
    }
}
