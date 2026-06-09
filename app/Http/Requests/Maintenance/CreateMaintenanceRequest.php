<?php

namespace App\Http\Requests\Maintenance;

use App\Models\Maintenance;
use Illuminate\Foundation\Http\FormRequest;

class CreateMaintenanceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'motorbike_id'          => 'required|integer|exists:motorbikes,id',
            'maintenance_date'      => 'required|date',
            'maintenance_type'      => 'required|string|in:' . implode(',', Maintenance::TYPES),
            'cost'                  => 'nullable|numeric|min:0',
            'description'           => 'nullable|string|max:500',
            'vendor_name'           => 'nullable|string|max:200',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
            'status'                => 'nullable|string|in:completed,pending,in_progress',
            'notes'                 => 'nullable|string|max:1000',
        ];
    }
}
