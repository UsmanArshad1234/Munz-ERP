<?php

namespace App\Http\Requests\PlatformIncome;

use App\Models\PlatformIncome;
use Illuminate\Foundation\Http\FormRequest;

class CreateIncomeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'income_date'   => 'required|date',
            'source_type'   => 'required|string|in:platform,rider',
            'platform_name' => 'required_if:source_type,platform|nullable|string|max:100',
            'employee_id'   => 'required_if:source_type,rider|nullable|integer|exists:employees,id',
            'amount'        => 'required|numeric|min:0.01',
            'description'   => 'nullable|string|max:500',
            'notes'         => 'nullable|string|max:1000',
        ];
    }
}
