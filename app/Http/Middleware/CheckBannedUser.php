<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBannedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is banned
            if ($user->isBanned()) {
                // Log out the banned user
                Auth::logout();

                // Invalidate the session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = 'Your account has been suspended.';

                if ($user->banned_reason) {
                    $message .= ' Reason: '.$user->banned_reason;
                }

                // Redirect to login with ban message
                return redirect()->route('login')
                    ->with('error', $message);
            }
        }

        return $next($request);
    }
}
