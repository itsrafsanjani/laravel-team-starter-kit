<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;

class AuthenticateUser
{
    public function execute(Request $request): User
    {
        $credentials = $request->only(['email', 'password']);

        if (! Auth::validate($credentials)) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                ['email' => [trans('auth.failed')]]
            );
        }

        $user = User::where('email', $credentials['email'])->first();

        if (Features::enabled(Features::twoFactorAuthentication()) && $user->hasEnabledTwoFactorAuthentication()) {
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $request->boolean('remember'),
            ]);

            throw new \Exception('Two-factor authentication required');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $user;
    }
}
