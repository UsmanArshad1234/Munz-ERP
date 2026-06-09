<?php

namespace App\Http\Requests\Fine;

use App\Models\Fine;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFineRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'fine_date'   => 'sometimes|date',
            'fine_type'   => 'sometimes|string|in:' . implode(',', Fine::TYPES),
            'amount'      => 'sometimes|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'notes'       => 'nullable|string|max:1000',
        ];
    }
}
