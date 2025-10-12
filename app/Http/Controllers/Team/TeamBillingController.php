<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamBillingController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $team = team();
        $this->authorize('viewBilling', $team);

        $activePlan = $team->getActivePlan();

        return Inertia::render('Teams/Settings/Billing', [
            'team' => $team,
            'plan' => $activePlan['plan'],
            'planCycle' => $activePlan['cycle'],
            'subscription' => $activePlan['subscription'],
        ]);
    }

    public function plans(Request $request)
    {
        $team = team();
        $this->authorize('viewBilling', $team);

        $plans = Plan::availableForNewCustomers()->get();

        return Inertia::render('Teams/Settings/Billing/Plans', [
            'team' => $team,
            'plans' => $plans,
        ]);
    }

    public function updateBillingSettings(Request $request)
    {
        $team = team();
        $this->authorize('manageBilling', $team);

        $request->validate([
            'billing_type' => 'required|in:person,company',
            'billing_name' => 'required|string|max:255',
            'billing_email' => 'nullable|email|max:255',
            'tax_id' => 'nullable|string|max:50',
        ]);

        $team->update([
            'billing_type' => $request->billing_type,
            'billing_name' => $request->billing_name,
            'billing_email' => $request->billing_email,
            'tax_id' => $request->tax_id,
        ]);

        if ($team->hasStripeId()) {
            $team->updateStripeCustomer([
                'email' => $request->billing_email,
                'name' => $request->billing_name,
            ]);
        }

        return redirect()->route('team.settings.billing', $team)
            ->with('success', 'Billing settings updated successfully.');
    }

    public function updateBillingAddress(Request $request)
    {
        $team = team();
        $this->authorize('manageBilling', $team);

        $request->validate([
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $team->update([
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
        ]);

        if ($team->hasStripeId()) {
            $team->updateStripeCustomer([
                'address' => [
                    'line1' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'postal_code' => $request->postal_code,
                    'country' => $request->country,
                ],
            ]);
        }

        return redirect()->route('team.settings.billing', $team)
            ->with('success', 'Billing address updated successfully.');
    }

    public function checkout(Request $request)
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

    public function billingPortal(Request $request)
    {
        $team = team();
        $this->authorize('manageBilling', $team);

        return $team->redirectToBillingPortal(route('team.settings.billing', $team));
    }
}
