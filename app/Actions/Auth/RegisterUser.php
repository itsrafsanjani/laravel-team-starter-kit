<?php

namespace App\Actions\Auth;

use App\Actions\Teams\AcceptTeamInvitation;
use App\Actions\Teams\CreateTeam;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterUser
{
    public function __construct(
        private CreateTeam $createTeam,
        private AcceptTeamInvitation $acceptTeamInvitation
    ) {}

    public function execute(Request $request): array
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Check if this is an invitation-based registration
        $invitationId = $request->get('invitation_id');
        if ($invitationId) {
            $invitation = TeamInvitation::with('team')
                ->where('id', $invitationId)
                ->where('email', $request->email)
                ->whereNull('accepted_at')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($invitation) {
                // Accept the invitation instead of creating a new team
                $this->acceptTeamInvitation->execute($invitation, $user);

                // Auto-verify email for invited users since invitation was sent to this email
                $user->markEmailAsVerified();

                event(new Registered($user));
                Auth::login($user);

                return [
                    'user' => $user,
                    'team' => $invitation->team,
                    'is_invitation' => true,
                    'message' => 'Welcome! You have successfully joined the team.',
                ];
            }
        }

        // Create a personal team for the new user (normal registration)
        $team = $this->createTeam->execute($user, [
            'name' => $user->name,
            'type' => 'personal',
            'description' => 'Personal team for '.$user->name,
        ]);

        event(new Registered($user));
        Auth::login($user);

        return [
            'user' => $user,
            'team' => $team,
            'is_invitation' => false,
        ];
    }
}
