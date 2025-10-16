<?php

namespace App\Http\Controllers\Team;

use App\Actions\Teams\CreateTeam;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function create()
    {
        return Inertia::render('Teams/Create');
    }

    public function store(Request $request, CreateTeam $createTeam)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9-]+$/|unique:teams,slug',
            'billing_email' => 'required|email|max:255',
        ], [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'This slug is already taken. Please choose a different one.',
        ]);

        $team = $createTeam->execute($request->user(), $request->all());

        return redirect()->route('team.settings.general', $team)
            ->with('success', 'Team created successfully.');
    }

    public function switch(Request $request)
    {
        $user = $request->user();
        $team = team();

        if (! $user->belongsToTeam($team)) {
            abort(403, 'You do not have access to this team.');
        }

        return redirect()->route('team.dashboard', $team)
            ->with('success', 'Switched to '.$team->name);
    }

    public function delete(Request $request)
    {
        $team = team();
        Gate::authorize('delete', $team);

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
