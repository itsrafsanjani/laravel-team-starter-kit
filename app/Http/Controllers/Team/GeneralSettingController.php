<?php

namespace App\Http\Controllers\Team;

use App\Actions\Teams\UpdateTeam;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class GeneralSettingController extends Controller
{
    public function index(Request $request)
    {
        $team = team();
        Gate::authorize('view', $team);

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

    public function update(Request $request, UpdateTeam $updateTeam)
    {
        $team = team();
        Gate::authorize('update', $team);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9-]+$/|unique:teams,slug,'.$team->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $updateTeam->execute($team, $request->user(), $request->all());

        return redirect()->route('team.settings.general', $team)
            ->with('success', 'Team settings updated successfully.');
    }
}
