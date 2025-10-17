<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class UpdateBillingSettingsController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request)
    {
        $team = team();
        $this->authorize('manageBilling', $team);

        $request->validate([
            'billing_type'  => 'required|in:person,company',
            'billing_name'  => 'required|string|max:255',
            'billing_email' => 'nullable|email|max:255',
            'tax_id'        => 'nullable|string|max:50',
        ]);

        $team->update([
            'billing_type'  => $request->billing_type,
            'billing_name'  => $request->billing_name,
            'billing_email' => $request->billing_email,
            'tax_id'        => $request->tax_id,
        ]);

        if ($team->hasStripeId()) {
            $team->updateStripeCustomer([
                'email' => $request->billing_email,
                'name'  => $request->billing_name,
            ]);
        }

        return redirect()->route('team.settings.billing', $team)
            ->with('success', 'Billing settings updated successfully.');
    }
}
