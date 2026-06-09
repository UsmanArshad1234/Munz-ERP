<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'type', 'value', 'label', 'sort_order', 'is_active', 'is_default',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    // ── Setting types ─────────────────────────────────────────────────────────
    const TYPES = [
        'job_title', 'department', 'employee_status', 'work_emirate',
        'zone', 'platform_name', 'salary_type', 'wps_status',
        'bike_status', 'assignment_status', 'loan_status',
        'fine_type', 'expense_category', 'income_type', 'payment_method',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
