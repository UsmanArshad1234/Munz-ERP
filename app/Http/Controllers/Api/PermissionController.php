<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PermissionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PermissionService $permissionService) {}

    // GET /api/permissions — all permissions grouped by module
    public function index(): JsonResponse
    {
        return $this->success($this->permissionService->getAllGrouped());
    }

    // GET /api/permissions/role/{role} — default permissions of a role
    public function rolePermissions(string $role): JsonResponse
    {
        if (!in_array($role, ['owner', 'superadmin', 'admin'])) {
            return $this->error('Invalid role', 422);
        }

        return $this->success([
            'role'        => $role,
            'permissions' => $this->permissionService->getRolePermissions($role),
        ]);
    }

    // PUT /api/permissions/role/{role} — update default permissions of a role
    public function updateRolePermissions(Request $request, string $role): JsonResponse
    {
        if ($role === 'owner') {
            return $this->error('Owner permissions cannot be modified', 403);
        }

        if (!in_array($role, ['superadmin', 'admin'])) {
            return $this->error('Invalid role', 422);
        }

        $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'string|exists:permissions,slug',
        ]);

        $this->permissionService->updateRolePermissions($role, $request->permissions);

        return $this->success([
            'role'        => $role,
            'permissions' => $this->permissionService->getRolePermissions($role),
        ], 'Role permissions updated');
    }

    // GET /api/permissions/user/{user} — effective permissions of a user
    public function userPermissions(User $user): JsonResponse
    {
        return $this->success($this->permissionService->getUserPermissions($user));
    }

    // PUT /api/permissions/user/{user} — set per-user permission overrides
    public function updateUserPermissions(Request $request, User $user): JsonResponse
    {
        if ($user->isOwner()) {
            return $this->error('Owner permissions cannot be modified', 403);
        }

        $request->validate([
            'overrides'          => 'required|array',
            'overrides.*.slug'   => 'required|string|exists:permissions,slug',
            'overrides.*.granted'=> 'required|boolean',
        ]);

        $this->permissionService->syncUserOverrides($user, $request->overrides);

        return $this->success(
            $this->permissionService->getUserPermissions($user),
            'User permissions updated'
        );
    }

    // DELETE /api/permissions/user/{user}/reset — remove all overrides, back to role defaults
    public function resetUserPermissions(User $user): JsonResponse
    {
        if ($user->isOwner()) {
            return $this->error('Owner permissions cannot be modified', 403);
        }

        $this->permissionService->resetToRoleDefaults($user);

        return $this->success(
            $this->permissionService->getUserPermissions($user),
            'User permissions reset to role defaults'
        );
    }

    // GET /api/auth/my-permissions — logged-in user's own effective permissions
    public function myPermissions(Request $request): JsonResponse
    {
        return $this->success([
            'role'        => $request->user()->role,
            'permissions' => $request->user()->getAllPermissions(),
        ]);
    }
}
