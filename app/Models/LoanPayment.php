<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    protected $fillable = [
        'loan_id', 'payment_date', 'payment_amount',
        'payment_method', 'payroll_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'payment_date'   => 'date',
        'payment_amount' => 'decimal:2',
    ];

    const METHOD_PAYROLL       = 'payroll';
    const METHOD_CASH          = 'cash';
    const METHOD_BANK_TRANSFER = 'bank_transfer';

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
