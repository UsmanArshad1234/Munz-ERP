<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use Auditable;

    protected function getAuditRef(): ?string
    {
        return $this->payroll_id ?? (string) $this->getKey();
    }

    protected $fillable = [
        'payroll_id', 'employee_id', 'month', 'year', 'salary_type',
        'gross_salary', 'loan_deduction', 'fine_deduction', 'salik_deduction',
        'penalty_deduction', 'other_deduction', 'total_deductions', 'net_salary',
        'attendance_days', 'hours_compliance', 'payment_status', 'payroll_status',
        'approved_by', 'approved_at', 'notes', 'created_by',
    ];

    protected $casts = [
        'gross_salary'     => 'decimal:2',
        'loan_deduction'   => 'decimal:2',
        'fine_deduction'   => 'decimal:2',
        'salik_deduction'  => 'decimal:2',
        'penalty_deduction'=> 'decimal:2',
        'other_deduction'  => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary'       => 'decimal:2',
        'approved_at'      => 'datetime',
        'month'            => 'integer',
        'year'             => 'integer',
    ];

    const PAYMENT_STATUS_UNPAID = 'unpaid';
    const PAYMENT_STATUS_PAID   = 'paid';

    const STATUS_DRAFT    = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function loanPayments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function getMonthNameAttribute(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    public function isApproved(): bool
    {
        return $this->payroll_status === self::STATUS_APPROVED;
    }

    public function isDraft(): bool
    {
        return $this->payroll_status === self::STATUS_DRAFT;
    }
}
