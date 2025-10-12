<?php

namespace App\Helpers;

use App\Models\Team;
use App\Models\User;
use App\Services\RolePermissionService;

if (! function_exists('user_can')) {
    /**
     * Check if user has permission for a team
     */
    function user_can(User $user, Team $team, string $permission): bool
    {
        return app(RolePermissionService::class)->can($user, $team, $permission);
    }
}

if (! function_exists('user_role')) {
    /**
     * Get user's role for a team
     */
    function user_role(User $user, Team $team): ?string
    {
        return $user->belongsToTeam($team) ? $user->teamRole($team) : null;
    }
}

if (! function_exists('user_permissions')) {
    /**
     * Get user's permissions for a team
     */
    function user_permissions(User $user, Team $team): array
    {
        return app(RolePermissionService::class)->getUserTeamPermissions($user, $team);
    }
}

if (! function_exists('role_info')) {
    /**
     * Get role information
     */
    function role_info(string $role): ?array
    {
        return app(RolePermissionService::class)->getRoleInfo($role);
    }
}

if (! function_exists('all_roles')) {
    /**
     * Get all available roles
     */
    function all_roles(): array
    {
        return app(RolePermissionService::class)->getAllRoles();
    }
}

if (! function_exists('all_permissions')) {
    /**
     * Get all available permissions
     */
    function all_permissions(): array
    {
        return app(RolePermissionService::class)->getAllPermissions();
    }
}
