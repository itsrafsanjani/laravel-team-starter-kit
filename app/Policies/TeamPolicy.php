<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use App\Traits\HasRolePermissions;

class TeamPolicy
{
    use HasRolePermissions;

    /**
     * Determine whether the user can view the team.
     */
    public function view(User $user, Team $team): bool
    {
        return $this->userHasTeamPermission($user, $team, 'view_team');
    }

    /**
     * Determine whether the user can update the team.
     */
    public function update(User $user, Team $team): bool
    {
        return $this->userHasTeamPermission($user, $team, 'update_team');
    }

    /**
     * Determine whether the user can delete the team.
     */
    public function delete(User $user, Team $team): bool
    {
        return $this->userHasTeamPermission($user, $team, 'delete_team');
    }

    /**
     * Determine whether the user can manage team members.
     */
    public function manageMembers(User $user, Team $team): bool
    {
        return $this->userHasTeamPermission($user, $team, 'manage_members');
    }

    /**
     * Determine whether the user can view billing information.
     */
    public function viewBilling(User $user, Team $team): bool
    {
        return $this->userHasTeamPermission($user, $team, 'view_billing');
    }

    /**
     * Determine whether the user can manage billing.
     */
    public function manageBilling(User $user, Team $team): bool
    {
        return $this->userHasTeamPermission($user, $team, 'manage_billing');
    }

    /**
     * Determine whether the user can invite members.
     */
    public function inviteMembers(User $user, Team $team): bool
    {
        return $this->userHasTeamPermission($user, $team, 'invite_members');
    }

    /**
     * Determine whether the user can remove members.
     */
    public function removeMembers(User $user, Team $team): bool
    {
        return $this->userHasTeamPermission($user, $team, 'remove_members');
    }

    /**
     * Determine whether the user can view team settings.
     */
    public function viewSettings(User $user, Team $team): bool
    {
        return $this->userHasTeamPermission($user, $team, 'view_settings');
    }

    /**
     * Determine whether the user can update team settings.
     */
    public function updateSettings(User $user, Team $team): bool
    {
        return $this->userHasTeamPermission($user, $team, 'update_settings');
    }
}
