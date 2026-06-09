<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserManagementService
{
    public function getAll(array $filters): Collection
    {
        $query = User::query();

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('name')->get(['id', 'name', 'email', 'phone', 'role', 'status', 'created_at']);
    }

    public function create(array $data): User
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'role'     => $data['role'],
            'password' => $data['password'],
        ]);
    }

    public function update(User $user, array $data): User
    {
        $fillable = array_filter(
            array_intersect_key($data, array_flip(['name', 'email', 'phone', 'role', 'status', 'password'])),
            fn($v) => $v !== null
        );

        $user->update($fillable);

        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function toggleStatus(User $user): User
    {
        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);

        return $user->fresh();
    }

    public function formatUser(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'role'       => $user->role,
            'status'     => $user->status,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}
