<?php

namespace App\Http\Controllers\Team;

use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\DeleteTeam;
use App\Actions\Teams\UpdateTeam;
use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
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
}
