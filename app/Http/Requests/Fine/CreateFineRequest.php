<?php

namespace App\Http\Requests\Fine;

use App\Models\Fine;
use Illuminate\Foundation\Http\FormRequest;

class CreateFineRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
            'fine_date'   => 'required|date',
            'fine_type'   => 'required|string|in:' . implode(',', Fine::TYPES),
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'notes'       => 'nullable|string|max:1000',
        ];
    }
}
