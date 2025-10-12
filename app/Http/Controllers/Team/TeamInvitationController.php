<?php

namespace App\Http\Controllers\Team;

use App\Actions\Teams\AcceptTeamInvitation;
use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TeamInvitationController extends Controller
{
    public function show(Request $request, TeamInvitation $invitation)
    {
        // Check if invitation is valid
        if ($invitation->isExpired()) {
            return Inertia::render('Teams/Invitations/Expired');
        }

        if ($invitation->isAccepted()) {
            return Inertia::render('Teams/Invitations/AlreadyAccepted');
        }

        $team = $invitation->team;
        $user = Auth::user();

        // If user is logged in, check if they can accept this invitation
        if ($user) {
            // Check if this invitation is for the logged-in user
            if ($user->email !== $invitation->email) {
                return Inertia::render('Teams/Invitations/WrongEmail', [
                    'invitation' => $invitation,
                    'userEmail' => $user->email,
                ]);
            }

            // Check if user is already a member
            if ($team->hasUser($user)) {
                return Inertia::render('Teams/Invitations/AlreadyMember', [
                    'team' => $team,
                ]);
            }

            // User is logged in and can accept the invitation
            return Inertia::render('Teams/Invitations/Accept', [
                'invitation' => $invitation,
                'team' => $team,
                'user' => $user,
            ]);
        }

        // User is not logged in, redirect to signup form with invitation ID only
        return redirect()->route('register', [
            'invitation_id' => $invitation->id,
        ]);
    }

    public function accept(Request $request, TeamInvitation $invitation, AcceptTeamInvitation $acceptTeamInvitation)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        try {
            $acceptTeamInvitation->execute($invitation, $user);

            return redirect()->route('team.dashboard', $invitation->team)
                ->with('success', 'You have successfully joined the team.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function decline(Request $request, TeamInvitation $invitation)
    {
        $invitation->delete();

        return redirect()->route('login')
            ->with('success', 'Invitation declined.');
    }
}
