<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * The permissions that belong to the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }

    /**
     * The users that belong to the role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if the role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()
            ->where('name', $permission)
            ->exists();
    }

    /**
     * Assign a permission to the role
     */
    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission)->firstOrFail();
        }

        return $this->permissions()->syncWithoutDetaching($permission);
    }

    /**
     * Remove a permission from the role
     */
    public function revokePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission)->firstOrFail();
        }

        return $this->permissions()->detach($permission);
    }

    /**
     * Sync role permissions
     */
    public function syncPermissions(array $permissions)
    {
        return $this->permissions()->sync($permissions);
    }
}
