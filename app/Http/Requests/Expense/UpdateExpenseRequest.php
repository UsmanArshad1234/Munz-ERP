<?php

namespace App\Http\Requests\Expense;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'expense_date' => 'sometimes|date',
            'category'     => 'sometimes|string|in:' . implode(',', Expense::CATEGORIES),
            'amount'       => 'sometimes|numeric|min:0.01',
            'description'  => 'nullable|string|max:500',
            'vendor_name'  => 'nullable|string|max:200',
            'notes'        => 'nullable|string|max:1000',
        ];
    }
}
