<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use App\Traits\HasRolePermissions;

class RolePermissionService
{
    use HasRolePermissions;

    /**
     * Get all available roles with their information
     */
    public function getAllRoles(): array
    {
        $roles = [];
        foreach ($this->getAvailableRoles() as $role) {
            $roles[$role] = $this->getRoleInfo($role);
        }

        return $roles;
    }

    /**
     * Get permissions for a specific role
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
     * Check if user has permission for a team
     */
    public function userHasTeamPermission(User $user, Team $team, string $permission): bool
    {
        if (! $user->belongsToTeam($team)) {
            return false;
        }

        $userRole = $user->teamRole($team);

        return $this->roleHasPermission($userRole, $permission);
    }

    /**
     * Get user's role information for a team
     */
    public function getUserTeamRoleInfo(User $user, Team $team): ?array
    {
        if (! $user->belongsToTeam($team)) {
            return null;
        }

        $role = $user->teamRole($team);

        return $this->getRoleInfo($role);
    }

    /**
     * Get user's permissions for a team
     */
    public function getUserTeamPermissions(User $user, Team $team): array
    {
        if (! $user->belongsToTeam($team)) {
            return [];
        }

        $role = $user->teamRole($team);

        return $this->getRolePermissions($role);
    }

    /**
     * Check if user can perform an action on a team
     */
    public function can(User $user, Team $team, string $permission): bool
    {
        return $this->userHasTeamPermission($user, $team, $permission);
    }

    /**
     * Get all permissions with descriptions
     */
    public function getAllPermissions(): array
    {
        return config('roles.permissions', []);
    }

    /**
     * Get role hierarchy (useful for UI display)
     */
    public function getRoleHierarchy(): array
    {
        return [
            'owner' => 3,
            'admin' => 2,
            'member' => 1,
        ];
    }

    /**
     * Check if one role is higher than another
     */
    public function isRoleHigher(string $role1, string $role2): bool
    {
        $hierarchy = $this->getRoleHierarchy();

        return ($hierarchy[$role1] ?? 0) > ($hierarchy[$role2] ?? 0);
    }
}
