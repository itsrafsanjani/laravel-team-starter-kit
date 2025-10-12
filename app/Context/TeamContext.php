<?php

namespace App\Context;

use App\Models\Team;
use App\Models\User;
use App\Services\RolePermissionService;
use Illuminate\Http\Request;

class TeamContext
{
    private ?Team $currentTeam = null;

    private ?User $user = null;

    private array $userTeams = [];

    private array $permissions = [];

    private bool $resolved = false;

    public function __construct(
        private RolePermissionService $rolePermissionService
    ) {}

    public function resolve(Request $request): self
    {
        if ($this->resolved) {
            return $this;
        }

        $this->user = $request->user();
        $this->currentTeam = $request->attributes->get('team');

        if ($this->user) {
            $this->userTeams = $this->user->teams()->get()->map(function ($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'slug' => $team->slug,
                    'type' => $team->type,
                    'logo' => $team->logo,
                ];
            })->toArray();

            // Fallback to session-remembered team or default team if no current team
            if (! $this->currentTeam && $this->user->hasAnyTeam()) {
                // Try to get the last visited team from session
                $lastTeamId = $request->session()->get('last_team_id');
                if ($lastTeamId) {
                    $lastTeam = $this->user->teams()->where('teams.id', $lastTeamId)->first();
                    if ($lastTeam) {
                        $this->currentTeam = $lastTeam;
                    }
                }

                // If no last team or user doesn't belong to it, use default team
                if (! $this->currentTeam) {
                    $this->currentTeam = $this->user->getDefaultTeam();
                }
            }
        }

        // Get permissions for current team
        if ($this->user && $this->currentTeam) {
            $this->permissions = $this->rolePermissionService->getUserTeamPermissions(
                $this->user,
                $this->currentTeam
            );
        }

        $this->resolved = true;

        return $this;
    }

    public function getCurrentTeam(): ?Team
    {
        return $this->currentTeam;
    }

    public function getCurrentTeamData(): ?array
    {
        if (! $this->currentTeam) {
            return null;
        }

        return [
            'id' => $this->currentTeam->id,
            'name' => $this->currentTeam->name,
            'slug' => $this->currentTeam->slug,
            'type' => $this->currentTeam->type,
            'logo' => $this->currentTeam->logo,
            'logo_url' => $this->currentTeam->logo_url,
        ];
    }

    public function getUserTeams(): array
    {
        return $this->userTeams;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    public function isTeamOwner(): bool
    {
        return $this->currentTeam && $this->user && $this->currentTeam->isOwner($this->user);
    }

    public function getTeamRole(): ?string
    {
        if (! $this->currentTeam || ! $this->user) {
            return null;
        }

        return $this->user->teamRole($this->currentTeam);
    }

    /**
     * Get user data formatted for frontend.
     */
    public function getUserData(): ?array
    {
        if (! $this->user) {
            return null;
        }

        return [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'email_verified_at' => $this->user->email_verified_at,
            'avatar' => $this->user->avatar,
            'current_team_id' => $this->user->current_team_id,
            'created_at' => $this->user->created_at,
            'updated_at' => $this->user->updated_at,
        ];
    }

    /**
     * Get all context data for Inertia sharing.
     */
    public function getInertiaData(): array
    {
        return [
            'auth' => [
                'user' => $this->getUserData(),
            ],
            'currentTeam' => $this->getCurrentTeamData(),
            'teams' => $this->getUserTeams(),
            'permissions' => $this->getPermissions(),
        ];
    }

    /**
     * Reset the context (useful for testing).
     */
    public function reset(): self
    {
        $this->currentTeam = null;
        $this->user = null;
        $this->userTeams = [];
        $this->permissions = [];
        $this->resolved = false;

        return $this;
    }
}
