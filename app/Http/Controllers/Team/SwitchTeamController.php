<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SwitchTeamController extends Controller
{
    public function __invoke(Request $request)
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
