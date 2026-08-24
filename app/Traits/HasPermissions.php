<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;

trait HasPermissions
{
    /**
     * Check if user has a specific role.
     */
    public function hasRole(string|array $roles): bool
    {
        if (empty($this->role)) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($this->role->name, $roles);
        }

        return $this->role->name === $roles;
    }

    /**
     * Check if user has a specific permission via their role.
     */
    public function hasPermission(string $permissionName): bool
    {
        // Administrator role has implicit access to all permissions
        if ($this->hasRole('Administrator')) {
            return true;
        }

        if (!$this->relationLoaded('role')) {
            $this->load('role.permissions');
        }

        if (!$this->role) {
            return false;
        }

        return $this->role->permissions->contains('name', $permissionName);
    }
}
