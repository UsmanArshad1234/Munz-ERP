<?php

namespace App\Http\Requests\Expense;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;

class CreateExpenseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'expense_date' => 'required|date',
            'category'     => 'required|string|in:' . implode(',', Expense::CATEGORIES),
            'amount'       => 'required|numeric|min:0.01',
            'description'  => 'nullable|string|max:500',
            'vendor_name'  => 'nullable|string|max:200',
            'notes'        => 'nullable|string|max:1000',
        ];
    }
}
