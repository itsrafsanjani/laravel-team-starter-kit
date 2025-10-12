<?php

namespace App\Http\Controllers\Team;

use App\Actions\Teams\UpdateTeam;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamSettingsController extends Controller
{
    use AuthorizesRequests;

    public function generalSettings(Request $request)
    {
        $team = $request->attributes->get('team');
        $this->authorize('view', $team);

        $user = $request->user();
        $userTeams = $user->teams()->count();
        $canDelete = $user->ownsTeam($team) || $user->teamRole($team) === 'owner';

        return Inertia::render('Teams/Settings/General', [
            'team' => $team,
            'userRole' => $user->teamRole($team),
            'canDelete' => $canDelete,
            'isOnlyTeam' => $userTeams <= 1,
        ]);
    }

    public function updateGeneralSettings(Request $request, UpdateTeam $updateTeam)
    {
        $team = $request->attributes->get('team');
        $this->authorize('update', $team);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9-]+$/|unique:teams,slug,'.$team->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $updateTeam->execute($team, $request->user(), $request->all());

        return redirect()->route('team.settings.general', $team)
            ->with('success', 'Team settings updated successfully.');
    }

    public function deleteTeam(Request $request)
    {
        $team = $request->attributes->get('team');
        $this->authorize('delete', $team);

        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = $request->user();

        // Check if this is the user's only team
        if ($user->teams()->count() <= 1) {
            return back()->withErrors(['password' => 'You cannot delete your only team.']);
        }

        // Check if user can delete this team
        if (! $user->ownsTeam($team) && $user->teamRole($team) !== 'owner') {
            return back()->withErrors(['password' => 'You do not have permission to delete this team.']);
        }

        // Delete the team
        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted successfully.');
    }
}
