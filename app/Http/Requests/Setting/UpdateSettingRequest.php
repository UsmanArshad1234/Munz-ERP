<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'label'      => 'sometimes|required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'sometimes|required|boolean',
        ];
    }
}
