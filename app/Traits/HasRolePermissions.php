<?php

namespace App\Traits;

trait HasRolePermissions
{
    /**
     * Get all permissions for a specific role
     */
    public function getRolePermissions(string $role): array
    {
        return config('roles.roles.'.$role.'.permissions', []);
    }

    /**
     * Check if a role has a specific permission
     */
    public function roleHasPermission(string $role, string $permission): bool
    {
        $permissions = $this->getRolePermissions($role);

        return in_array($permission, $permissions);
    }

    /**
     * Get all available roles
     */
    public function getAvailableRoles(): array
    {
        return array_keys(config('roles.roles', []));
    }

    /**
     * Get role information (name, description, permissions)
     */
    public function getRoleInfo(string $role): ?array
    {
        return config('roles.roles.'.$role, null);
    }

    /**
     * Get all permissions with their descriptions
     */
    public function getAllPermissions(): array
    {
        return config('roles.permissions', []);
    }

    /**
     * Check if user has permission for a team
     */
    public function userHasTeamPermission($user, $team, string $permission): bool
    {
        if (! $user->belongsToTeam($team)) {
            return false;
        }

        $userRole = $user->teamRole($team);

        return $this->roleHasPermission($userRole, $permission);
    }
}
