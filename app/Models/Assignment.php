<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    use Auditable;

    protected function getAuditRef(): ?string
    {
        return $this->assignment_id ?? (string) $this->getKey();
    }

    protected $fillable = [
        'assignment_id', 'employee_id', 'motorbike_id',
        'start_date', 'return_date', 'handover_condition',
        'return_condition', 'status', 'remarks',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'return_date' => 'date',
    ];

    const STATUS_ACTIVE         = 'active';
    const STATUS_RETURNED       = 'returned';
    const STATUS_PENDING_RETURN = 'pending_return';
    const STATUS_CANCELLED      = 'cancelled';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function motorbike(): BelongsTo
    {
        return $this->belongsTo(Motorbike::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
