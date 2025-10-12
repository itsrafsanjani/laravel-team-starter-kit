<?php

use App\Models\Subscription;
use App\Models\Team;

beforeEach(function () {
    $this->team = Team::factory()->create();
});

it('can be created', function () {
    $subscription = $this->team->subscriptions()->create([
        'name' => 'default',
        'stripe_id' => 'sub_test',
        'stripe_status' => 'active',
        'stripe_price' => 'price_test',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => null,
    ]);

    expect($subscription)->toBeInstanceOf(Subscription::class);
});

it('can check if lifetime subscription', function () {
    $lifetimeSubscription = $this->team->subscriptions()->create([
        'name' => 'default',
        'stripe_id' => 'sub_test',
        'stripe_status' => 'active',
        'stripe_price' => 'price_test',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => null,
    ]);

    $trialSubscription = $this->team->subscriptions()->create([
        'name' => 'default',
        'stripe_id' => 'sub_test2',
        'stripe_status' => 'active',
        'stripe_price' => 'price_test',
        'quantity' => 1,
        'trial_ends_at' => now()->addDays(7),
        'ends_at' => null,
    ]);

    $endingSubscription = $this->team->subscriptions()->create([
        'name' => 'default',
        'stripe_id' => 'sub_test3',
        'stripe_status' => 'active',
        'stripe_price' => 'price_test',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => now()->addDays(30),
    ]);

    expect($lifetimeSubscription->isLifetime())->toBeTrue();
    expect($trialSubscription->isLifetime())->toBeFalse();
    expect($endingSubscription->isLifetime())->toBeFalse();
});
