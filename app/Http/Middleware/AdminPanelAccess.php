<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPanelAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (! $user) {
            return redirect()->route('login');
        }

        // Check if user has admin panel access
        if (! $user->canAccessAdminPanel()) {
            abort(403, 'Access denied. You do not have permission to access the admin panel.');
        }

        return $next($request);
    }
}
