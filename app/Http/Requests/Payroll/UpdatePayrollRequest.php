<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'gross_salary'      => 'sometimes|numeric|min:0',
            'loan_deduction'    => 'nullable|numeric|min:0',
            'fine_deduction'    => 'nullable|numeric|min:0',
            'salik_deduction'   => 'nullable|numeric|min:0',
            'penalty_deduction' => 'nullable|numeric|min:0',
            'other_deduction'   => 'nullable|numeric|min:0',
            'attendance_days'   => 'nullable|integer|min:0|max:31',
            'hours_compliance'  => 'nullable|integer|min:0|max:100',
            'notes'             => 'nullable|string|max:1000',
        ];
    }
}
