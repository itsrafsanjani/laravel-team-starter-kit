<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamBillingIndexController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request)
    {
        $team = team();
        $this->authorize('viewBilling', $team);

        $activePlan = $team->getActivePlan();

        return Inertia::render('Teams/Settings/Billing', [
            'team'         => $team,
            'plan'         => $activePlan['plan'],
            'planCycle'    => $activePlan['cycle'],
            'subscription' => $activePlan['subscription'],
        ]);
    }
}
