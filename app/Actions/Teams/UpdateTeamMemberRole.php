<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateTeamMemberRole
{
    public function execute(Team $team, User $currentUser, User $member, string $role): void
    {
        // Ensure the current user can manage members
        if (! $currentUser->can('manageMembers', $team)) {
            throw new \Exception('You do not have permission to manage team members.');
        }

        // Ensure the member is actually part of the team
        if (! $team->users()->where('user_id', $member->id)->exists()) {
            throw new \Exception('User is not a member of this team.');
        }

        // Prevent changing the owner's role
        if ($member->teamRole($team) === 'owner') {
            throw new \Exception('Cannot change the role of the team owner.');
        }

        // Validate the role
        $availableRoles = array_keys(config('roles.roles', []));
        if (! in_array($role, $availableRoles)) {
            throw new \Exception('Invalid role specified.');
        }

        DB::transaction(function () use ($team, $member, $role) {
            $team->users()->updateExistingPivot($member->id, [
                'role' => $role,
                'updated_at' => now(),
            ]);
        });
    }
}
