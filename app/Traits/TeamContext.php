<?php

namespace App\Traits;

use App\Models\Team;
use Illuminate\Http\Request;

trait TeamContext
{
    protected function getCurrentTeam(Request $request): ?Team
    {
        return $request->attributes->get('team');
    }

    protected function getCurrentTeamId(Request $request): ?string
    {
        $team = $this->getCurrentTeam($request);

        return $team?->id;
    }

    protected function getCurrentTeamSlug(Request $request): ?string
    {
        $team = $this->getCurrentTeam($request);

        return $team?->slug;
    }

    protected function isTeamOwner(Request $request): bool
    {
        $team = $this->getCurrentTeam($request);
        $user = $request->user();

        return $team && $user && $team->isOwner($user);
    }

    protected function getTeamRole(Request $request): ?string
    {
        $team = $this->getCurrentTeam($request);
        $user = $request->user();

        return $team && $user ? $user->teamRole($team) : null;
    }

    protected function hasTeamPermission(Request $request, string $permission): bool
    {
        $role = $this->getTeamRole($request);

        return match ($role) {
            'owner' => true,
            'admin' => in_array($permission, ['manage_members', 'manage_settings', 'view_billing']),
            'member' => in_array($permission, ['view_team']),
            default => false,
        };
    }
}
