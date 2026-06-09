<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(string $email, string $password): array|false
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            return false;
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$user->isActive()) {
            Auth::logout();
            return ['inactive' => true];
        }

        $token = $user->createToken('muzn-erp-token')->plainTextToken;

        return [
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => $this->formatUser($user),
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    public function getProfile(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'role'       => $user->role,
            'status'     => $user->status,
            'created_at' => $user->created_at,
        ];
    }

    public function updateProfile(User $user, array $data): array
    {
        $user->update(array_filter($data, fn($v) => $v !== null));

        return $this->formatUser($user->fresh());
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!password_verify($currentPassword, $user->password)) {
            return false;
        }

        $user->update(['password' => $newPassword]);

        return true;
    }

    private function formatUser(User $user): array
    {
        return [
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'phone'  => $user->phone,
            'role'   => $user->role,
            'status' => $user->status,
        ];
    }
}
