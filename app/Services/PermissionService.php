<?php

namespace App\Services;

use App\Enums\PermissionEnum;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    public function getAllGrouped(): array
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get();

        $grouped = [];
        foreach ($permissions as $perm) {
            $grouped[$perm->module][] = [
                'id'     => $perm->id,
                'slug'   => $perm->slug,
                'name'   => $perm->name,
                'module' => $perm->module,
            ];
        }

        return $grouped;
    }

    public function getRolePermissions(string $role): array
    {
        if ($role === 'owner') {
            return Permission::pluck('slug')->toArray();
        }

        return DB::table('role_permissions')
                 ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                 ->where('role_permissions.role', $role)
                 ->pluck('permissions.slug')
                 ->toArray();
    }

    public function updateRolePermissions(string $role, array $permissionSlugs): void
    {
        // Owner role cannot be modified
        if ($role === 'owner') return;

        $permissionIds = Permission::whereIn('slug', $permissionSlugs)->pluck('id');

        DB::table('role_permissions')->where('role', $role)->delete();

        $rows = $permissionIds->map(fn($id) => [
            'role'          => $role,
            'permission_id' => $id,
            'created_at'    => now(),
            'updated_at'    => now(),
        ])->toArray();

        if (!empty($rows)) {
            DB::table('role_permissions')->insert($rows);
        }
    }

    public function getUserPermissions(User $user): array
    {
        return [
            'role'             => $user->role,
            'role_permissions' => $this->getRolePermissions($user->role),
            'overrides'        => $user->permissions()
                                       ->get()
                                       ->map(fn($p) => [
                                           'slug'    => $p->slug,
                                           'name'    => $p->name,
                                           'module'  => $p->module,
                                           'granted' => (bool) $p->pivot->granted,
                                       ])
                                       ->toArray(),
            'effective'        => $user->getAllPermissions(),
        ];
    }

    public function syncUserOverrides(User $user, array $overrides): void
    {
        // overrides = [['slug' => 'employees.create', 'granted' => true], ...]
        $slugToId = Permission::whereIn('slug', array_column($overrides, 'slug'))
                              ->pluck('id', 'slug');

        $syncData = [];
        foreach ($overrides as $override) {
            $permId = $slugToId[$override['slug']] ?? null;
            if ($permId) {
                $syncData[$permId] = ['granted' => (bool) $override['granted']];
            }
        }

        $user->permissions()->sync($syncData);
    }

    public function revokeAllOverrides(User $user): void
    {
        $user->permissions()->detach();
    }

    public function resetToRoleDefaults(User $user): void
    {
        $this->revokeAllOverrides($user);
    }
}
