<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'payment_date'   => 'required|date',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|in:payroll,cash,bank_transfer',
            'payroll_id'     => 'nullable|integer|exists:payrolls,id',
            'notes'          => 'nullable|string|max:1000',
        ];
    }
}
