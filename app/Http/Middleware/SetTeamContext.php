<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetTeamContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Get team slug from route parameter
            $teamSlug = $request->route('team');

            if ($teamSlug) {
                $team = Team::where('slug', $teamSlug)->first();

                if ($team && $user->belongsToTeam($team)) {
                    $request->attributes->set('team', $team);

                    // Only update session if this is a different team than the last one
                    $lastTeamId = $request->session()->get('last_team_id');
                    if ($lastTeamId !== $team->id) {
                        $request->session()->put('last_team_id', $team->id);
                    }
                } else {
                    abort(403, 'You do not have access to this team.');
                }
            } else {
                // For non-team routes, try to use the last visited team from session
                $lastTeamId = $request->session()->get('last_team_id');
                $team = null;

                if ($lastTeamId) {
                    $team = Team::where('id', $lastTeamId)
                        ->whereHas('users', function ($query) use ($user) {
                            $query->where('user_id', $user->id);
                        })
                        ->first();
                }

                // If no last team or user doesn't belong to it, use default team
                if (! $team && $user->hasAnyTeam()) {
                    $team = $user->getDefaultTeam();
                }

                // Set the team in request attributes for Inertia context
                if ($team) {
                    $request->attributes->set('team', $team);
                }
            }
        }

        return $next($request);
    }
}
