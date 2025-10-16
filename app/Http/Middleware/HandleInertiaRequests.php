<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $currentTeam = team();

        // Get user teams data
        $userTeams = [];
        $currentTeamData = null;
        $permissions = [];

        if ($user) {
            $userTeams = $user->teams()->get()->map(function ($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'slug' => $team->slug,
                    'type' => $team->type,
                    'logo' => $team->logo,
                ];
            })->toArray();

            if ($currentTeam) {
                $currentTeamData = [
                    'id' => $currentTeam->id,
                    'name' => $currentTeam->name,
                    'slug' => $currentTeam->slug,
                    'type' => $currentTeam->type,
                    'logo' => $currentTeam->logo,
                ];

                // Get permissions for current team
                $rolePermissionService = app(\App\Services\RolePermissionService::class);
                $permissions = $rolePermissionService->getUserTeamPermissions($user, $currentTeam);
            }
        }

        // Add admin permissions if user is on admin routes
        $adminPermissions = null;
        if ($request->is('admin*') && $user) {
            $adminPermissions = $this->getUserAdminPermissions($user);
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'avatar' => $user->avatar,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ] : null,
            ],
            'currentTeam' => $currentTeamData,
            'teams' => $userTeams,
            'permissions' => $permissions,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'adminPermissions' => $adminPermissions,
        ];
    }

    private function getUserAdminPermissions($user)
    {
        // Get user's admin role with its permissions
        $adminRole = $user->adminRole()->first();

        if (! $adminRole || ! $adminRole->permissions) {
            return [];
        }

        $permissions = is_string($adminRole->permissions)
            ? json_decode($adminRole->permissions, true)
            : $adminRole->permissions;

        return is_array($permissions) ? $permissions : [];
    }
}
