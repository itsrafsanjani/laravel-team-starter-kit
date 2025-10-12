<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;

class VerifyEmail
{
    public function execute(EmailVerificationRequest $request): bool
    {
        $user = Auth::user();

        if ($user instanceof User && $user->hasVerifiedEmail()) {
            return true;
        }

        $request->fulfill();

        return true;
    }
}
