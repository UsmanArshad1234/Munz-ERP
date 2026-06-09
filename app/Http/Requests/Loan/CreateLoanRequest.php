<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;

class CreateLoanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employee_id'            => 'required|integer|exists:employees,id',
            'loan_date'              => 'required|date',
            'loan_amount'            => 'required|numeric|min:1',
            'monthly_deduction'      => 'nullable|numeric|min:0',
            'number_of_installments' => 'nullable|integer|min:1|max:120',
            'notes'                  => 'nullable|string|max:1000',
        ];
    }
}
