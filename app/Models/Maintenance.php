<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use Auditable;

    protected $table = 'maintenance';

    protected function getAuditRef(): ?string
    {
        return $this->maintenance_id ?? (string) $this->getKey();
    }

    protected $fillable = [
        'maintenance_id', 'motorbike_id', 'maintenance_date', 'maintenance_type',
        'cost', 'description', 'vendor_name', 'receipt_path',
        'next_maintenance_date', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'maintenance_date'      => 'date',
        'next_maintenance_date' => 'date',
        'cost'                  => 'decimal:2',
    ];

    const TYPE_OIL_CHANGE      = 'oil_change';
    const TYPE_TIRE            = 'tire';
    const TYPE_BRAKE           = 'brake';
    const TYPE_ENGINE          = 'engine';
    const TYPE_ACCIDENT_REPAIR = 'accident_repair';
    const TYPE_GENERAL         = 'general';
    const TYPE_OTHER           = 'other';

    const STATUS_COMPLETED   = 'completed';
    const STATUS_PENDING     = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';

    const TYPES = [
        self::TYPE_OIL_CHANGE, self::TYPE_TIRE, self::TYPE_BRAKE,
        self::TYPE_ENGINE, self::TYPE_ACCIDENT_REPAIR, self::TYPE_GENERAL, self::TYPE_OTHER,
    ];

    public function motorbike(): BelongsTo
    {
        return $this->belongsTo(Motorbike::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
