<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    use Auditable;

    protected function getAuditRef(): ?string
    {
        return $this->fine_id ?? (string) $this->getKey();
    }

    protected $fillable = [
        'fine_id', 'employee_id', 'fine_date', 'fine_type', 'amount',
        'description', 'receipt_path', 'payroll_id', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'fine_date' => 'date',
        'amount'    => 'decimal:2',
    ];

    const TYPE_SALIK           = 'salik';
    const TYPE_TRAFFIC_FINE    = 'traffic_fine';
    const TYPE_COMPANY_PENALTY = 'company_penalty';
    const TYPE_OTHER           = 'other';

    const STATUS_PENDING  = 'pending';
    const STATUS_DEDUCTED = 'deducted';
    const STATUS_WAIVED   = 'waived';

    const TYPES = [
        self::TYPE_SALIK,
        self::TYPE_TRAFFIC_FINE,
        self::TYPE_COMPANY_PENALTY,
        self::TYPE_OTHER,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
