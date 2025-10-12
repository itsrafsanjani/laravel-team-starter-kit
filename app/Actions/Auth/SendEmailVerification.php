<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;

class SendEmailVerification
{
    public function execute(Request $request): string
    {
        if ($request->user()->hasVerifiedEmail()) {
            throw new \Exception('Email already verified');
        }

        $request->user()->sendEmailVerificationNotification();

        return 'verification-link-sent';
    }
}
