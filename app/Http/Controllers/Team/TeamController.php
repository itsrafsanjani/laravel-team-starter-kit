<?php

namespace App\Http\Controllers\Team;

use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\DeleteTeam;
use App\Actions\Teams\UpdateTeam;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Traits\TeamContext;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamController extends Controller
{
    use TeamContext;

    public function index(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Teams/Index', [
            'teams' => $user->teams()->with('owner')->get(),
        ]);
    }

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

    public function update(Request $request, Team $team, UpdateTeam $updateTeam)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:personal,company',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        try {
            $updateTeam->execute($team, $request->user(), $request->all());

            return redirect()->route('team.settings.general', $team)
                ->with('success', 'Team updated successfully.');
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function destroy(Request $request, Team $team, DeleteTeam $deleteTeam)
    {
        try {
            $deleteTeam->execute($team, $request->user());

            return redirect()->route('teams.index')
                ->with('success', 'Team deleted successfully.');
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function switch(Request $request, Team $team)
    {
        $user = $request->user();

        if (! $user->belongsToTeam($team)) {
            abort(403, 'You do not have access to this team.');
        }

        return redirect()->route('team.dashboard', $team)
            ->with('success', 'Switched to '.$team->name);
    }
}
