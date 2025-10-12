<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUser;
use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        private RegisterUser $registerUser
    ) {}

    /**
     * Show the registration page.
     */
    public function create(Request $request): Response
    {
        $invitationId = $request->get('invitation_id');

        // If there's an invitation ID, find the invitation
        $invitation = null;
        if ($invitationId) {
            $invitation = TeamInvitation::with('team')
                ->where('id', $invitationId)
                ->whereNull('accepted_at')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();
        }

        return Inertia::render('Auth/Register', [
            'invitation' => $invitation,
            'prefilledEmail' => $invitation?->email,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $result = $this->registerUser->execute($request);

        if ($result['is_invitation']) {
            return redirect()->route('team.dashboard', $result['team'])
                ->with('success', $result['message']);
        }

        // Redirect to the team dashboard instead of the general dashboard
        return redirect()->intended(route('team.dashboard', $result['team'], absolute: false));
    }
}
