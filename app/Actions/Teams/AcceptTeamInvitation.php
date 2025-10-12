<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcceptTeamInvitation
{
    public function execute(TeamInvitation $invitation, User $user): bool
    {
        return DB::transaction(function () use ($invitation, $user) {
            // Check if invitation is valid
            if ($invitation->isExpired()) {
                throw new \InvalidArgumentException('This invitation has expired.');
            }

            if ($invitation->isAccepted()) {
                throw new \InvalidArgumentException('This invitation has already been accepted.');
            }

            // Check if user email matches invitation email
            if ($user->email !== $invitation->email) {
                throw new \InvalidArgumentException('This invitation is not for your email address.');
            }

            // Check if user is already a member
            if ($invitation->team->hasUser($user)) {
                throw new \InvalidArgumentException('You are already a member of this team.');
            }

            // Add user to team
            $invitation->team->users()->attach($user->id, [
                'role' => $invitation->role,
                'joined_at' => now(),
            ]);

            // Accepted the invitation, so can be deleted
            $invitation->delete();

            return true;
        });
    }
}
