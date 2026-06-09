<?php

namespace App\Http\Requests\PlatformIncome;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncomeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'income_date'   => 'sometimes|date',
            'source_type'   => 'sometimes|string|in:platform,rider',
            'platform_name' => 'nullable|string|max:100',
            'employee_id'   => 'nullable|integer|exists:employees,id',
            'amount'        => 'sometimes|numeric|min:0.01',
            'description'   => 'nullable|string|max:500',
            'notes'         => 'nullable|string|max:1000',
        ];
    }
}
