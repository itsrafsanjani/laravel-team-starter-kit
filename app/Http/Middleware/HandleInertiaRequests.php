<?php

namespace App\Http\Middleware;

use App\Facades\TeamContext;
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
        // Resolve team context using the facade
        $teamContext = TeamContext::resolve($request);

        // Get all team context data
        $contextData = $teamContext->getInertiaData();

        // Add admin permissions if user is on admin routes
        $adminPermissions = null;
        if ($request->is('admin*') && $request->user()) {
            $adminPermissions = $this->getUserAdminPermissions($request->user());
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            ...$contextData,
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
