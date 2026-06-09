<?php

namespace App\Http\Requests\Setting;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSettingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'       => ['required', 'string', Rule::in(Setting::TYPES)],
            'label'      => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}
