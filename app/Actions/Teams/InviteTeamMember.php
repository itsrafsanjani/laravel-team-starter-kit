<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class InviteTeamMember
{
    public function execute(Team $team, User $user, string $email, string $role = 'member'): TeamInvitation
    {
        return DB::transaction(function () use ($team, $user, $email, $role) {
            // Check if user can invite to this team
            if (! $this->canInviteToTeam($team, $user)) {
                throw new \InvalidArgumentException('You do not have permission to invite members to this team.');
            }

            // Check if user is already a member
            $existingUser = User::where('email', $email)->first();
            if ($existingUser && $team->hasUser($existingUser)) {
                throw new \InvalidArgumentException('This user is already a member of the team.');
            }

            // Check if there's already a pending invitation
            $existingInvitation = $team->invitations()
                ->where('email', $email)
                ->whereNull('accepted_at')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($existingInvitation) {
                throw new \InvalidArgumentException('An invitation has already been sent to this email address.');
            }

            // Create the invitation
            $invitation = $team->invitations()->create([
                'email' => $email,
                'role' => $role,
                'expires_at' => now()->addDays(7), // 7 days expiry
            ]);

            // Send invitation notification
            Notification::route('mail', $email)->notify(new TeamInvitationNotification($invitation));

            return $invitation;
        });
    }

    private function canInviteToTeam(Team $team, User $user): bool
    {
        return $team->isOwner($user) || in_array($user->teamRole($team), ['admin', 'owner']);
    }
}
