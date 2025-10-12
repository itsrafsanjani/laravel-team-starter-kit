<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RemoveTeamMember
{
    public function execute(Team $team, User $remover, User $userToRemove): bool
    {
        return DB::transaction(function () use ($team, $remover, $userToRemove) {
            // Check if remover can remove members from this team
            if (! $this->canRemoveFromTeam($team, $remover)) {
                throw new \InvalidArgumentException('You do not have permission to remove members from this team.');
            }

            // Cannot remove the team owner
            if ($team->isOwner($userToRemove)) {
                throw new \InvalidArgumentException('Cannot remove the team owner.');
            }

            // Cannot remove yourself
            if ($remover->id === $userToRemove->id) {
                throw new \InvalidArgumentException('You cannot remove yourself from the team.');
            }

            // Remove the user from the team
            $removed = $team->users()->detach($userToRemove->id);

            return $removed > 0;
        });
    }

    private function canRemoveFromTeam(Team $team, User $user): bool
    {
        return $team->isOwner($user) || in_array($user->teamRole($team), ['admin', 'owner']);
    }
}
