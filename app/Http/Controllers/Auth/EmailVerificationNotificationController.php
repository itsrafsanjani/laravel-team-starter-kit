<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendEmailVerification;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function __construct(
        private SendEmailVerification $sendEmailVerification
    ) {}

    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $this->sendEmailVerification->execute($request);

            return back()->with('status', 'verification-link-sent');
        } catch (\Exception $e) {
            return redirect()->intended(route('dashboard', absolute: false));
        }
    }
}
