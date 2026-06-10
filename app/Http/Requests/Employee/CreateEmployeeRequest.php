<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class CreateEmployeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                    => 'required|string|max:255',
            'mobile'                  => 'nullable|string|max:20',
            'email'                   => 'nullable|email|max:255',
            'nationality'             => 'nullable|string|max:100',
            'job_title'               => 'nullable|string|max:100',
            'department'              => 'nullable|string|max:100',
            'status'                  => 'nullable|string|max:50',
            'work_emirate'            => 'nullable|string|max:100',
            'zone'                    => 'nullable|string|max:100',
            'platform_name'           => 'nullable|string|max:100',
            'platform_id'             => 'nullable|string|max:100',
            'salary_amount'           => 'nullable|numeric|min:0',
            'salary_type'             => 'nullable|string|max:50',
            'wps_status'              => 'nullable|in:wps,no_wps',
            'passport_number'         => 'nullable|string|max:50',
            'passport_expiry'         => 'nullable|date',
            'emirates_id'             => 'nullable|string|max:50',
            'emirates_id_expiry'      => 'nullable|date',
            'visa_expiry'             => 'nullable|date',
            'labour_card_expiry'      => 'nullable|date',
            'driving_license'         => 'nullable|string|max:50',
            'driving_license_expiry'  => 'nullable|date',
            'notes'                   => 'nullable|string',
        ];
    }
}
