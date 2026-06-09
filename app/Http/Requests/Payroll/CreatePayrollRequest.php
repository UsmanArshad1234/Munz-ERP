<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class CreatePayrollRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employee_id'       => 'required|integer|exists:employees,id',
            'month'             => 'required|integer|min:1|max:12',
            'year'              => 'required|integer|min:2020|max:2099',
            'salary_type'       => 'nullable|string|in:monthly,daily',
            'gross_salary'      => 'required|numeric|min:0',
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
