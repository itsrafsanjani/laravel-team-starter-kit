<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamBillingCheckoutController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request)
    {
        $team = team();
        $this->authorize('manageBilling', $team);

        $validated = $request->validate([
            'plan_id' => ['required', 'integer'],
            'period' => ['required', 'in:monthly,yearly,lifetime'],
        ]);

        $plan = Plan::find($validated['plan_id']);
        $priceId = $plan->getStripePriceIdForCycle($validated['period']);

        if (! $priceId) {
            return redirect()->back()
                ->with('error', 'Invalid plan or period.');
        }

        $subscription = $team->newSubscription('default', $priceId)
            ->withMetadata([
                'team_id' => $team->id,
                'environment' => config('app.env'),
                'site_url' => url('/'),
                'plan_id' => $plan->id,
            ]);

        $checkoutOptions = [
            'success_url' => route('team.settings.billing', [$team, 'success' => 'true']),
            'cancel_url' => route('team.settings.billing', [$team, 'success' => 'false']),
        ];

        $checkout = $subscription->checkout($checkoutOptions, [
            'name' => $team->name,
            'email' => $team->billing_email,
        ]);

        return Inertia::location($checkout->url);
    }
}
