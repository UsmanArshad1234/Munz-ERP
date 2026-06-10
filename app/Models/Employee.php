<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes, Auditable;

    protected function getAuditRef(): ?string
    {
        return $this->employee_id ?? (string) $this->getKey();
    }

    protected $fillable = [
        'employee_id', 'name', 'mobile', 'email', 'nationality',
        'job_title', 'department', 'status', 'work_emirate', 'zone',
        'platform_name', 'platform_id', 'salary_amount', 'salary_type',
        'wps_status', 'passport_number', 'passport_expiry', 'passport_document',
        'emirates_id', 'emirates_id_expiry', 'visa_expiry', 'visa_document',
        'labour_card_expiry', 'driving_license', 'driving_license_expiry',
        'assigned_bike_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'passport_expiry'         => 'date',
        'emirates_id_expiry'      => 'date',
        'visa_expiry'             => 'date',
        'labour_card_expiry'      => 'date',
        'driving_license_expiry'  => 'date',
        'salary_amount'           => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Expiry helpers ────────────────────────────────────────────────────────

    public function getExpiryStatus(): array
    {
        $today = now()->startOfDay();
        $fields = [
            'passport'        => $this->passport_expiry,
            'emirates_id'     => $this->emirates_id_expiry,
            'visa'            => $this->visa_expiry,
            'labour_card'     => $this->labour_card_expiry,
            'driving_license' => $this->driving_license_expiry,
        ];

        $status = [];
        foreach ($fields as $key => $date) {
            if (!$date) continue;
            $daysLeft = $today->diffInDays($date, false);
            $status[$key] = [
                'expiry_date' => $date->toDateString(),
                'days_left'   => $daysLeft,
                'status'      => match (true) {
                    $daysLeft < 0  => 'expired',
                    $daysLeft <= 15 => 'critical',
                    $daysLeft <= 30 => 'warning',
                    default         => 'ok',
                },
            ];
        }

        return $status;
    }
}
