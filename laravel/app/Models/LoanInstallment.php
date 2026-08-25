<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanInstallment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'opening_balance' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
