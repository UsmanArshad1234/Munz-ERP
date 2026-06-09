<?php

namespace App\Http\Requests\Maintenance;

use App\Models\Maintenance;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'maintenance_date'      => 'sometimes|date',
            'maintenance_type'      => 'sometimes|string|in:' . implode(',', Maintenance::TYPES),
            'cost'                  => 'nullable|numeric|min:0',
            'description'           => 'nullable|string|max:500',
            'vendor_name'           => 'nullable|string|max:200',
            'next_maintenance_date' => 'nullable|date',
            'status'                => 'sometimes|string|in:completed,pending,in_progress',
            'notes'                 => 'nullable|string|max:1000',
        ];
    }
}
