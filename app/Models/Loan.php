<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use Auditable;

    protected function getAuditRef(): ?string
    {
        return $this->loan_id ?? (string) $this->getKey();
    }

    protected $fillable = [
        'loan_id', 'employee_id', 'loan_date', 'loan_amount', 'paid_amount',
        'remaining_balance', 'monthly_deduction', 'number_of_installments',
        'remaining_installments', 'status', 'notes', 'attachment_path', 'created_by',
    ];

    protected $casts = [
        'loan_date'              => 'date',
        'loan_amount'            => 'decimal:2',
        'paid_amount'            => 'decimal:2',
        'remaining_balance'      => 'decimal:2',
        'monthly_deduction'      => 'decimal:2',
        'number_of_installments' => 'integer',
        'remaining_installments' => 'integer',
    ];

    const STATUS_ACTIVE    = 'active';
    const STATUS_PAID      = 'paid';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ON_HOLD   = 'on_hold';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
