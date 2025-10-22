<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamBillingPlansController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request)
    {
        $team = team();
        $this->authorize('viewBilling', $team);

        $plans = Plan::availableForNewCustomers()->get();

        return Inertia::render('Teams/Settings/Billing/Plans', [
            'team'  => $team,
            'plans' => $plans,
        ]);
    }
}
