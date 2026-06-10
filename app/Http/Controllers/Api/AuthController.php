<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->email, $request->password);

        if ($result === false) {
            return $this->error('Invalid credentials', 401);
        }

        if (isset($result['inactive'])) {
            return $this->error('Your account is inactive. Contact administrator.', 403);
        }

        return $this->success($result, 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logged out successfully');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return $this->success(null, 'All sessions logged out');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success($this->authService->getProfile($request->user()));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name'            => 'sometimes|required|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'profile_picture' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = $this->authService->updateProfile(
            $request->user(),
            $request->only('name', 'phone'),
            $request->file('profile_picture')
        );

        return $this->success($user, 'Profile updated');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $changed = $this->authService->changePassword(
            $request->user(),
            $request->current_password,
            $request->password
        );

        if (!$changed) {
            return $this->error('Current password is incorrect', 400);
        }

        return $this->success(null, 'Password changed successfully');
    }
}
