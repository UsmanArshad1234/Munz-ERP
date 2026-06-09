<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
                    ->withPivot('granted')
                    ->withTimestamps();
    }

    // ── Role helpers ──────────────────────────────────────────────────────────

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasElevatedAccess(): bool
    {
        return in_array($this->role, ['owner', 'superadmin']);
    }

    // ── Permission check ──────────────────────────────────────────────────────

    public function hasPermission(string $slug): bool
    {
        // Owner always has everything
        if ($this->isOwner()) {
            return true;
        }

        // Check per-user override first (explicit grant or revoke)
        $override = $this->permissions()
                         ->where('slug', $slug)
                         ->first();

        if ($override !== null) {
            return (bool) $override->pivot->granted;
        }

        // Fall back to role default permissions
        return \DB::table('role_permissions')
                  ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                  ->where('role_permissions.role', $this->role)
                  ->where('permissions.slug', $slug)
                  ->exists();
    }

    public function getAllPermissions(): array
    {
        if ($this->isOwner()) {
            return \App\Models\Permission::pluck('slug')->toArray();
        }

        // Role default permissions
        $rolePerms = \DB::table('role_permissions')
                        ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                        ->where('role_permissions.role', $this->role)
                        ->pluck('permissions.slug')
                        ->toArray();

        // Per-user overrides
        $userPerms = $this->permissions()->get();

        foreach ($userPerms as $perm) {
            if ($perm->pivot->granted && !in_array($perm->slug, $rolePerms)) {
                $rolePerms[] = $perm->slug;
            } elseif (!$perm->pivot->granted) {
                $rolePerms = array_filter($rolePerms, fn($s) => $s !== $perm->slug);
            }
        }

        return array_values($rolePerms);
    }
}
