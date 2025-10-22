<?php

namespace App\Http\Controllers\Team;

use App\Actions\Teams\InviteTeamMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InviteMemberController extends Controller
{
    public function __invoke(Request $request, InviteTeamMember $inviteTeamMember)
    {
        $team = team();
        Gate::authorize('inviteMembers', $team);

        $availableRoles = array_keys(config('roles.roles'));

        $request->validate([
            'email' => 'required|email|max:255',
            'role' => 'required|in:'.implode(',', $availableRoles),
        ]);

        try {
            $inviteTeamMember->execute($team, $request->user(), $request->email, $request->role);

            return redirect()->back()
                ->with('success', 'Invitation sent successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['email' => $e->getMessage()]);
        }
    }
}
