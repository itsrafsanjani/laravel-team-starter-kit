<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteTeam
{
    public function execute(Team $team, User $user): bool
    {
        return DB::transaction(function () use ($team, $user) {
            // Check if user can delete this team
            if (! $this->canDeleteTeam($team, $user)) {
                throw new \InvalidArgumentException('You do not have permission to delete this team.');
            }

            // Remove all team members
            $team->users()->detach();

            // Delete all invitations
            $team->invitations()->delete();

            // Delete the team
            return $team->delete();
        });
    }

    private function canDeleteTeam(Team $team, User $user): bool
    {
        return $team->isOwner($user);
    }
}
