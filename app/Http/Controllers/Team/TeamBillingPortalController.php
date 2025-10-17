<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TeamBillingPortalController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request)
    {
        $team = team();
        $this->authorize('manageBilling', $team);

        return $team->redirectToBillingPortal(route('team.settings.billing', $team));
    }
}
