<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly UserManagementService $userService) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getAll($request->only('role', 'status', 'search'));

        return $this->success($users);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return $this->created($this->userService->formatUser($user), 'User created successfully');
    }

    public function show(User $user): JsonResponse
    {
        return $this->success($this->userService->formatUser($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updated = $this->userService->update($user, $request->validated());

        return $this->success($this->userService->formatUser($updated), 'User updated successfully');
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return $this->error('Cannot delete your own account', 400);
        }

        $this->userService->delete($user);

        return $this->success(null, 'User deleted successfully');
    }

    public function toggleStatus(User $user): JsonResponse
    {
        $updated = $this->userService->toggleStatus($user);

        return $this->success(
            ['id' => $updated->id, 'status' => $updated->status],
            "User {$updated->status} successfully"
        );
    }
}
