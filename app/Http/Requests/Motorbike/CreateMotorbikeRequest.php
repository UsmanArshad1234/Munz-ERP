<?php

namespace App\Http\Requests\Motorbike;

use Illuminate\Foundation\Http\FormRequest;

class CreateMotorbikeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'plate_number'      => 'required|string|max:20|unique:motorbikes,plate_number',
            'plate_code'        => 'nullable|string|max:20',
            'emirate'           => 'nullable|string|max:100',
            'zone'              => 'nullable|string|max:100',
            'bike_type'         => 'nullable|string|max:100',
            'brand'             => 'nullable|string|max:100',
            'model'             => 'nullable|string|max:100',
            'year'              => 'nullable|integer|min:2000|max:2030',
            'color'             => 'nullable|string|max:50',
            'chassis_number'    => 'nullable|string|max:50',
            'engine_number'     => 'nullable|string|max:50',
            'insurance_company' => 'nullable|string|max:100',
            'insurance_expiry'  => 'nullable|date',
            'mulkiya_expiry'    => 'nullable|date',
            'status'            => 'nullable|string|max:50',
            'notes'             => 'nullable|string',
        ];
    }
}
