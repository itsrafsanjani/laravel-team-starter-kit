<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $teamSlug = $request->route('team');

        if ($teamSlug) {
            $team = Team::where('slug', $teamSlug)->first();

            if (! $team) {
                abort(404, 'Team not found.');
            }

            // Set the team in the request for easy access
            $request->attributes->set('team', $team);
        }

        return $next($request);
    }
}
