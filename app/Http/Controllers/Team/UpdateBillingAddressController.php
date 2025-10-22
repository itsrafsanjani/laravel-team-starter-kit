<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class UpdateBillingAddressController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request)
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
}
