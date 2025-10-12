<?php

declare(strict_types=1);

namespace App\Listeners\Stripe;

use App\Models\Plan;
use App\Models\Team;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived as StripeWebhookReceived;

class WebhookReceived
{
    /**
     * Handle the event.
     */
    public function handle(StripeWebhookReceived $event): void
    {
        $eventType = $event->payload['type'];
        $eventData = $event->payload['data']['object'];

        match (true) {
            $eventType === 'checkout.session.completed' => $this->handleLtdCheckout($eventData),
            $eventType === 'customer.updated' => $this->handleCustomerUpdated($eventData),
            $eventType === 'radar.early_fraud_warning.created' => $this->handleEarlyFraudWarning($eventData),
            default => Log::info('Received unknown or unhandled event type: '.$eventType),
        };
    }

    private function handleLtdCheckout(array $payload)
    {
        // If the mode is setup, we don't need to do anything
        if ($payload['mode'] === 'setup') {
            return;
        }

        /** @var ?Team $team */
        $team = Team::query()
            ->where('stripe_id', $payload['customer'])
            ->first();

        if (! $team) {
            return;
        }

        $planId = $payload['metadata']['plan_id'] ?? null;
        if (! $planId) {
            return;
        }

        $plan = Plan::find($planId);

        if ($plan) {
            // $team->update([
            //     'type' => 'lifetime',
            //     'ltd_plan' => $plan['id'],
            //     'permission' => $plan['permission'],
            //     'trial_ends_at' => null,
            // ]);

            // LifetimePlanPurchased::dispatch($team, $plan);
        }
    }

    private function handleCustomerUpdated(array $payload): void
    {
        $customerId = $payload['id'];
        $team = Team::query()->where('stripe_id', $customerId)->first();

        if (! $team) {
            return;
        }

        $team->update([
            'pm_type' => isset($payload['default_source']['brand']) ? $payload['default_source']['brand'] : null,
            'pm_last_four' => isset($payload['default_source']['last4']) ? $payload['default_source']['last4'] : null,
            'city' => isset($payload['address']['city']) ? $payload['address']['city'] : null,
            'state' => isset($payload['address']['state']) ? $payload['address']['state'] : null,
            'postal_code' => isset($payload['address']['postal_code']) ? $payload['address']['postal_code'] : null,
            'country' => isset($payload['address']['country']) ? $payload['address']['country'] : null,
            'address' => isset($payload['address']['line1']) ? $payload['address']['line1'] : null,
            'billing_email' => isset($payload['email']) ? $payload['email'] : $team->billing_email,
        ]);
    }

    private function handleEarlyFraudWarning(array $payload): void
    {
        $chargeId = $payload['charge'];
        $appName = config('app.name');
        $environment = strtoupper(app()->environment());
        $stripeUrl = "https://dashboard.stripe.com/payments/{$chargeId}";

        $fraudType = $payload['fraud_type'] ?? 'Unknown';
        $actionable = isset($payload['actionable']) && $payload['actionable'] ? 'Yes' : 'No';

        $message = <<<MSG
*[$environment]* $appName
⚠️ Early Fraud Warning Detected

• *Charge:* <{$stripeUrl}|{$chargeId}>
• *Fraud Type:* `{$fraudType}`
• *Actionable:* `{$actionable}`

This charge has been flagged by Stripe's Radar as potentially fraudulent.
Please review immediately.
MSG;

        Log::channel('slack')->warning($message);
    }
}
