<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Motorbike extends Model
{
    use SoftDeletes, Auditable;

    protected function getAuditRef(): ?string
    {
        return $this->bike_id ?? (string) $this->getKey();
    }

    protected $fillable = [
        'bike_id', 'plate_number', 'plate_code', 'emirate', 'zone',
        'bike_type', 'brand', 'model', 'year', 'color',
        'chassis_number', 'engine_number', 'insurance_company',
        'insurance_expiry', 'mulkiya_expiry', 'status',
        'current_rider_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'insurance_expiry' => 'date',
        'mulkiya_expiry'   => 'date',
        'year'             => 'integer',
    ];

    public function currentRider(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'current_rider_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(Assignment::class)->where('status', 'active');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MotorbikeDocument::class);
    }

    public function getExpiryStatus(): array
    {
        $today  = now()->startOfDay();
        $fields = [
            'insurance' => $this->insurance_expiry,
            'mulkiya'   => $this->mulkiya_expiry,
        ];

        $status = [];
        foreach ($fields as $key => $date) {
            if (!$date) continue;
            $daysLeft = $today->diffInDays($date, false);
            $status[$key] = [
                'expiry_date' => $date->toDateString(),
                'days_left'   => $daysLeft,
                'status'      => match (true) {
                    $daysLeft < 0   => 'expired',
                    $daysLeft <= 15 => 'critical',
                    $daysLeft <= 30 => 'warning',
                    default         => 'ok',
                },
            ];
        }

        return $status;
    }
}
