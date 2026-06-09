<?php

namespace App\Http\Requests\Assignment;

use Illuminate\Foundation\Http\FormRequest;

class ReturnBikeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'return_date'      => 'nullable|date',
            'return_condition' => 'nullable|in:good,fair,poor',
            'remarks'          => 'nullable|string|max:500',
        ];
    }
}
