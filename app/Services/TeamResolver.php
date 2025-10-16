<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamResolver
{
    private ?Team $team = null;

    private bool $resolved = false;

    public function __construct(
        private Request $request
    ) {}

    /**
     * Reset the resolver state (useful for testing).
     */
    public function reset(): void
    {
        $this->team = null;
        $this->resolved = false;
    }

    /**
     * Get the current team for the request.
     */
    public function get(): ?Team
    {
        if ($this->resolved) {
            return $this->team;
        }

        $this->resolve();

        return $this->team;
    }

    /**
     * Check if a team has been resolved.
     */
    public function hasTeam(): bool
    {
        return $this->get() !== null;
    }

    /**
     * Set the team for the current request.
     */
    public function set(?Team $team): void
    {
        $this->team = $team;
        $this->resolved = true;
    }

    /**
     * Resolve the team from the request.
     */
    private function resolve(): void
    {
        $this->resolved = true;

        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();
        $teamSlug = $this->request->route('team');

        if ($teamSlug) {
            // Team-specific route - resolve from slug
            $this->team = Team::where('slug', $teamSlug)->first();

            if (! $this->team) {
                abort(404, 'Team not found.');
            }

            if (! $user->belongsToTeam($this->team)) {
                abort(403, 'You do not have access to this team.');
            }

            // Update session cache
            if ($this->request->session()->get('last_team_id') !== $this->team->id) {
                $this->request->session()->put('last_team_id', $this->team->id);
            }
        } else {
            // Non-team route - use cached team
            $this->resolveFromSession($user);
        }
    }

    /**
     * Resolve team from session cache.
     */
    private function resolveFromSession(User $user): void
    {
        $lastTeamId = $this->request->session()->get('last_team_id');

        if ($lastTeamId) {
            $this->team = Team::where('id', $lastTeamId)
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->first();
        }

        // Fallback to default team
        if (! $this->team && $user->hasAnyTeam()) {
            $this->team = $user->getDefaultTeam();

            if ($this->team) {
                $this->request->session()->put('last_team_id', $this->team->id);
            }
        }
    }
}
