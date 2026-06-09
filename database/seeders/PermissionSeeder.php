<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed all permissions
        foreach (PermissionEnum::all() as $perm) {
            Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                [
                    'name'   => $perm['name'],
                    'module' => $perm['module'],
                ]
            );
        }

        // 2. Seed role_permissions for superadmin and admin
        foreach (['superadmin', 'admin'] as $role) {
            $slugs = PermissionEnum::forRole($role);
            $permissionIds = Permission::whereIn('slug', $slugs)->pluck('id');

            foreach ($permissionIds as $permId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role' => $role, 'permission_id' => $permId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
