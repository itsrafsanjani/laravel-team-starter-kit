<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CleanupUserTeams
{
    /**
     * Clean up teams when a user is deleted.
     * Deletes teams where the user is the only member.
     * Transfers ownership to the first member for teams with other members.
     */
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Get all teams owned by the user
            $ownedTeams = $user->ownedTeams;

            foreach ($ownedTeams as $team) {
                // Check if this team has any members in the pivot table
                $membersCount = $team->users()->count();

                // If no members in pivot table, delete the team
                if ($membersCount === 0) {
                    $this->deleteTeam($team);
                } else {
                    // Transfer ownership to the first member
                    $firstMember = $team->users()->first();
                    $team->update(['user_id' => $firstMember->id]);
                }
            }

            // Remove user from all teams they're a member of (but don't own)
            $user->teams()->detach();
        });
    }

    /**
     * Delete a team and all its related data.
     */
    private function deleteTeam(Team $team): void
    {
        // Remove all team members
        $team->users()->detach();

        // Delete all invitations
        $team->invitations()->delete();

        // Delete the team
        $team->delete();
    }
}
