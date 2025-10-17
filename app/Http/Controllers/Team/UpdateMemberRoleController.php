<?php

namespace App\Http\Controllers\Team;

use App\Actions\Teams\UpdateTeamMemberRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UpdateMemberRoleController extends Controller
{
    public function __invoke(Request $request, $team, $user, UpdateTeamMemberRole $updateTeamMemberRole)
    {
        $team = team();
        Gate::authorize('manageMembers', $team);

        // Resolve the user if it's an ID
        if (is_numeric($user)) {
            $user = User::findOrFail($user);
        }

        $availableRoles = array_keys(config('roles.roles'));

        $request->validate([
            'role' => 'required|in:'.implode(',', $availableRoles),
        ]);

        try {
            $updateTeamMemberRole->execute($team, $request->user(), $user, $request->role);

            return redirect()->back()
                ->with('success', 'Member role updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
