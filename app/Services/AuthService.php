<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'id'                  => $user->id,
            'name'                => $user->name,
            'email'               => $user->email,
            'phone'               => $user->phone,
            'profile_picture_url' => $user->profile_picture
                                        ? asset('storage/' . $user->profile_picture)
                                        : null,
            'role'                => $user->role,
            'status'              => $user->status,
            'created_at'          => $user->created_at,
        ];
    }

    public function updateProfile(User $user, array $data, ?UploadedFile $picture = null): array
    {
        if ($picture) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $data['profile_picture'] = $picture->store("users/{$user->id}/avatar", 'public');
        }

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
            'id'                  => $user->id,
            'name'                => $user->name,
            'email'               => $user->email,
            'phone'               => $user->phone,
            'profile_picture_url' => $user->profile_picture
                                        ? asset('storage/' . $user->profile_picture)
                                        : null,
            'role'                => $user->role,
            'status'              => $user->status,
        ];
    }
}
