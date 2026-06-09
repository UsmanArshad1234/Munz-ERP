<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id', 'document_type', 'original_name',
        'file_path', 'mime_type', 'file_size', 'expiry_date', 'uploaded_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'file_size'   => 'integer',
    ];

    const TYPES = [
        'passport_copy', 'emirates_id_copy', 'driving_license_copy',
        'visa_copy', 'labour_card_copy', 'photo', 'contract', 'other',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
