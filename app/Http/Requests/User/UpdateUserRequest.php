<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name'     => 'sometimes|required|string|max:255',
            'email'    => "sometimes|required|email|unique:users,email,{$userId}",
            'phone'    => 'nullable|string|max:20',
            'role'     => 'sometimes|required|in:owner,superadmin,admin',
            'status'   => 'sometimes|required|in:active,inactive',
            'password' => 'sometimes|nullable|string|min:8|confirmed',
        ];
    }
}
