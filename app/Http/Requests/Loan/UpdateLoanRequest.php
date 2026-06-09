<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLoanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'loan_date'              => 'sometimes|date',
            'monthly_deduction'      => 'nullable|numeric|min:0',
            'number_of_installments' => 'nullable|integer|min:1|max:120',
            'status'                 => 'sometimes|string|in:active,paid,cancelled,on_hold',
            'notes'                  => 'nullable|string|max:1000',
        ];
    }
}
