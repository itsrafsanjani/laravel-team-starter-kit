<?php

use App\Enums\PlanType;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->plan = Plan::factory()->active()->create(['is_legacy' => false]);
});

it('can be created with factory', function () {
    expect($this->plan)->toBeInstanceOf(Plan::class);
    expect($this->plan->name)->not->toBeEmpty();
    expect($this->plan->slug)->not->toBeEmpty();
});

it('has correct fillable attributes', function () {
    $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'monthly_price',
        'yearly_price',
        'lifetime_price',
        'stripe_monthly_price_id',
        'stripe_yearly_price_id',
        'stripe_lifetime_price_id',
        'trial_days',
        'features',
        'permissions',
        'is_active',
        'is_popular',
        'is_legacy',
        'sort_order',
    ];

    expect($this->plan->getFillable())->toBe($fillable);
});

it('has correct casts', function () {
    expect($this->plan->getCasts())->toHaveKey('type', PlanType::class);
    expect($this->plan->getCasts())->toHaveKey('features', 'array');
    expect($this->plan->getCasts())->toHaveKey('permissions', 'array');
    expect($this->plan->getCasts())->toHaveKey('is_active', 'boolean');
    expect($this->plan->getCasts())->toHaveKey('is_popular', 'boolean');
    expect($this->plan->getCasts())->toHaveKey('is_legacy', 'boolean');
    expect($this->plan->getCasts())->toHaveKey('monthly_price', 'decimal:2');
    expect($this->plan->getCasts())->toHaveKey('yearly_price', 'decimal:2');
    expect($this->plan->getCasts())->toHaveKey('lifetime_price', 'decimal:2');
});

it('can check if free plan', function () {
    $freePlan = Plan::factory()->create(['type' => PlanType::FREE]);
    $paidPlan = Plan::factory()->create(['type' => PlanType::SUBSCRIPTION]);

    expect($freePlan->isFree())->toBeTrue();
    expect($paidPlan->isFree())->toBeFalse();
});

it('can check if trial plan', function () {
    $trialPlan = Plan::factory()->create(['type' => PlanType::TRIAL]);
    $paidPlan  = Plan::factory()->create(['type' => PlanType::SUBSCRIPTION]);

    expect($trialPlan->isTrial())->toBeTrue();
    expect($paidPlan->isTrial())->toBeFalse();
});

it('can check if subscription plan', function () {
    $subscriptionPlan = Plan::factory()->create(['type' => PlanType::SUBSCRIPTION]);
    $freePlan         = Plan::factory()->create(['type' => PlanType::FREE]);

    expect($subscriptionPlan->isSubscription())->toBeTrue();
    expect($freePlan->isSubscription())->toBeFalse();
});

it('can check if lifetime plan', function () {
    $lifetimePlan     = Plan::factory()->create(['type' => PlanType::LIFETIME]);
    $subscriptionPlan = Plan::factory()->create(['type' => PlanType::SUBSCRIPTION]);

    expect($lifetimePlan->isLifetime())->toBeTrue();
    expect($subscriptionPlan->isLifetime())->toBeFalse();
});

it('can check if has trial', function () {
    $planWithTrial    = Plan::factory()->create(['trial_days' => 14]);
    $planWithoutTrial = Plan::factory()->create(['trial_days' => 0]);

    expect($planWithTrial->hasTrial())->toBeTrue();
    expect($planWithoutTrial->hasTrial())->toBeFalse();
});

it('can get price for cycle', function () {
    $plan = Plan::factory()->create([
        'monthly_price'  => 10.00,
        'yearly_price'   => 100.00,
        'lifetime_price' => 500.00,
    ]);

    expect($plan->getPriceForCycle('monthly'))->toBe(10.00);
    expect($plan->getPriceForCycle('yearly'))->toBe(100.00);
    expect($plan->getPriceForCycle('lifetime'))->toBe(500.00);
    expect($plan->getPriceForCycle('invalid'))->toBeNull();
});

it('can get stripe price id for cycle', function () {
    $plan = Plan::factory()->create([
        'stripe_monthly_price_id'  => 'price_monthly',
        'stripe_yearly_price_id'   => 'price_yearly',
        'stripe_lifetime_price_id' => 'price_lifetime',
    ]);

    expect($plan->getStripePriceIdForCycle('monthly'))->toBe('price_monthly');
    expect($plan->getStripePriceIdForCycle('yearly'))->toBe('price_yearly');
    expect($plan->getStripePriceIdForCycle('lifetime'))->toBe('price_lifetime');
    expect($plan->getStripePriceIdForCycle('invalid'))->toBeNull();
});

it('can get cycle for price id', function () {
    $plan = Plan::factory()->create([
        'stripe_monthly_price_id'  => 'price_monthly',
        'stripe_yearly_price_id'   => 'price_yearly',
        'stripe_lifetime_price_id' => 'price_lifetime',
    ]);

    expect($plan->getCycleForPriceId('price_monthly'))->toBe('monthly');
    expect($plan->getCycleForPriceId('price_yearly'))->toBe('yearly');
    expect($plan->getCycleForPriceId('price_lifetime'))->toBe('lifetime');
    expect($plan->getCycleForPriceId('price_unknown'))->toBeNull();
});

it('can scope active plans', function () {
    $activePlan   = Plan::factory()->create(['is_active' => true]);
    $inactivePlan = Plan::factory()->create(['is_active' => false]);

    $activePlans = Plan::active()->get();

    expect($activePlans)->toHaveCount(2); // $this->plan (from beforeEach) + $activePlan
    expect($activePlans->pluck('id')->toArray())->toContain($activePlan->id);
    expect($activePlans->pluck('id')->toArray())->toContain($this->plan->id);
});

it('can scope by type', function () {
    $freePlan         = Plan::factory()->create(['type' => PlanType::FREE]);
    $subscriptionPlan = Plan::factory()->create(['type' => PlanType::SUBSCRIPTION]);

    $freePlans = Plan::byType(PlanType::FREE)->get();

    // Count existing free plans (including from beforeEach if it's FREE type)
    $expectedCount = $this->plan->type === PlanType::FREE ? 2 : 1;

    expect($freePlans)->toHaveCount($expectedCount);
    expect($freePlans->pluck('id')->toArray())->toContain($freePlan->id);
});

it('can scope ordered plans', function () {
    // Ensure the plan from beforeEach comes last by setting a high sort_order
    $this->plan->update(['sort_order' => 10]);

    $plan1 = Plan::factory()->create(['sort_order' => 2, 'name' => 'Plan B']);
    $plan2 = Plan::factory()->create(['sort_order' => 1, 'name' => 'Plan A']);
    $plan3 = Plan::factory()->create(['sort_order' => 1, 'name' => 'Plan C']);

    $orderedPlans = Plan::ordered()->get();

    expect($orderedPlans->first()->id)->toBe($plan2->id);
    expect($orderedPlans->skip(1)->first()->id)->toBe($plan3->id);
    expect($orderedPlans->skip(2)->first()->id)->toBe($plan1->id);
    expect($orderedPlans->last()->id)->toBe($this->plan->id); // $this->plan from beforeEach
});

it('can check if has permission', function () {
    $this->plan->update(['permissions' => ['feature1', 'feature2']]);

    expect($this->plan->hasPermission('feature1'))->toBeTrue();
    expect($this->plan->hasPermission('feature2'))->toBeTrue();
    expect($this->plan->hasPermission('feature3'))->toBeFalse();
});

it('can get permission value', function () {
    $this->plan->update(['permissions' => ['max_users' => 10, 'storage' => '1GB']]);

    expect($this->plan->getPermissionValue('max_users'))->toBe(10);
    expect($this->plan->getPermissionValue('storage'))->toBe('1GB');
    expect($this->plan->getPermissionValue('unknown'))->toBeNull();
    expect($this->plan->getPermissionValue('unknown', 'default'))->toBe('default');
});

it('can check if legacy', function () {
    $legacyPlan  = Plan::factory()->create(['is_legacy' => true]);
    $currentPlan = Plan::factory()->create(['is_legacy' => false]);

    expect($legacyPlan->isLegacy())->toBeTrue();
    expect($currentPlan->isLegacy())->toBeFalse();
});

it('can scope current plans', function () {
    $currentPlan  = Plan::factory()->create(['is_legacy' => false, 'is_active' => true]);
    $legacyPlan   = Plan::factory()->create(['is_legacy' => true, 'is_active' => true]);
    $inactivePlan = Plan::factory()->create(['is_legacy' => false, 'is_active' => false]);

    $currentPlans = Plan::current()->get();

    expect($currentPlans)->toHaveCount(2); // $this->plan (from beforeEach) + $currentPlan
    expect($currentPlans->pluck('id')->toArray())->toContain($currentPlan->id);
    expect($currentPlans->pluck('id')->toArray())->toContain($this->plan->id);
});

it('can scope legacy plans', function () {
    $currentPlan = Plan::factory()->create(['is_legacy' => false]);
    $legacyPlan  = Plan::factory()->create(['is_legacy' => true]);

    $legacyPlans = Plan::legacy()->get();

    expect($legacyPlans)->toHaveCount(1);
    expect($legacyPlans->first()->id)->toBe($legacyPlan->id);
});

it('can scope available for new customers', function () {
    $availablePlan = Plan::factory()->create(['is_legacy' => false, 'is_active' => true]);
    $legacyPlan    = Plan::factory()->create(['is_legacy' => true, 'is_active' => true]);
    $inactivePlan  = Plan::factory()->create(['is_legacy' => false, 'is_active' => false]);

    $availablePlans = Plan::availableForNewCustomers()->get();

    expect($availablePlans)->toHaveCount(2); // $this->plan (from beforeEach) + $availablePlan
    expect($availablePlans->pluck('id')->toArray())->toContain($availablePlan->id);
    expect($availablePlans->pluck('id')->toArray())->toContain($this->plan->id);
});

it('handles null permissions gracefully', function () {
    $this->plan->update(['permissions' => null]);

    expect($this->plan->hasPermission('any_permission'))->toBeFalse();
    expect($this->plan->getPermissionValue('any_permission'))->toBeNull();
    expect($this->plan->getPermissionValue('any_permission', 'default'))->toBe('default');
});

it('handles empty permissions array', function () {
    $this->plan->update(['permissions' => []]);

    expect($this->plan->hasPermission('any_permission'))->toBeFalse();
    expect($this->plan->getPermissionValue('any_permission'))->toBeNull();
});
