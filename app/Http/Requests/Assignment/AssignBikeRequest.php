<?php

namespace App\Http\Requests\Assignment;

use Illuminate\Foundation\Http\FormRequest;

class AssignBikeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employee_id'        => 'required|integer|exists:employees,id',
            'motorbike_id'       => 'required|integer|exists:motorbikes,id',
            'start_date'         => 'nullable|date',
            'handover_condition' => 'nullable|in:good,fair,poor',
            'remarks'            => 'nullable|string|max:500',
        ];
    }
}
