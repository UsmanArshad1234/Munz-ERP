<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformIncome extends Model
{
    use Auditable;

    protected function getAuditRef(): ?string
    {
        return $this->income_id ?? (string) $this->getKey();
    }

    protected $fillable = [
        'income_id', 'income_date', 'source_type', 'platform_name',
        'employee_id', 'amount', 'description', 'receipt_path', 'notes', 'created_by',
    ];

    protected $casts = [
        'income_date' => 'date',
        'amount'      => 'decimal:2',
    ];

    const SOURCE_PLATFORM = 'platform';
    const SOURCE_RIDER    = 'rider';

    const PLATFORMS = ['Talabat', 'Careem', 'Noon', 'InDrive', 'Other'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
