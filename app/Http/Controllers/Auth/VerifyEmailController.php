<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\VerifyEmail;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __construct(
        private VerifyEmail $verifyEmail
    ) {}

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $this->verifyEmail->execute($request);

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
