<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use Auditable;

    protected function getAuditRef(): ?string
    {
        return $this->expense_id ?? (string) $this->getKey();
    }

    protected $fillable = [
        'expense_id', 'expense_date', 'category', 'amount', 'description',
        'vendor_name', 'receipt_path', 'status', 'approved_by', 'approved_at',
        'notes', 'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
        'approved_at'  => 'datetime',
    ];

    const CATEGORY_FUEL        = 'fuel';
    const CATEGORY_MAINTENANCE = 'maintenance';
    const CATEGORY_OFFICE      = 'office';
    const CATEGORY_SALARY      = 'salary';
    const CATEGORY_SALIK       = 'salik';
    const CATEGORY_OTHER       = 'other';

    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const CATEGORIES = [
        self::CATEGORY_FUEL,
        self::CATEGORY_MAINTENANCE,
        self::CATEGORY_OFFICE,
        self::CATEGORY_SALARY,
        self::CATEGORY_SALIK,
        self::CATEGORY_OTHER,
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
