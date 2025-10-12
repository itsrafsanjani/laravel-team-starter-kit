<?php

use App\Models\Plan;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = Team::factory()->create(['user_id' => $this->user->id]);
});

it('can be created with factory', function () {
    expect($this->team)->toBeInstanceOf(Team::class);
    expect($this->team->name)->not->toBeEmpty();
    expect($this->team->slug)->not->toBeEmpty();
});

it('has correct casts', function () {
    expect($this->team->getCasts())->toHaveKey('settings', 'array');
    expect($this->team->getCasts())->toHaveKey('trial_ends_at', 'datetime');
});

it('generates slug automatically when creating', function () {
    $team = Team::create([
        'user_id' => $this->user->id,
        'name' => 'My Awesome Team',
    ]);

    expect($team->slug)->toBe('my-awesome-team');
});

it('can have owner', function () {
    expect($this->team->owner)->toBeInstanceOf(User::class);
    expect($this->team->owner->id)->toBe($this->user->id);
});

it('can have users', function () {
    $member = User::factory()->create();
    $this->team->users()->attach($member->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->team->users)->toHaveCount(1);
    expect($this->team->users->first())->toBeInstanceOf(User::class);
    expect($this->team->users->first()->id)->toBe($member->id);
});

it('can have invitations', function () {
    $invitation = TeamInvitation::factory()->create(['team_id' => $this->team->id]);

    expect($this->team->invitations)->toHaveCount(1);
    expect($this->team->invitations->first())->toBeInstanceOf(TeamInvitation::class);
    expect($this->team->invitations->first()->id)->toBe($invitation->id);
});

it('can get members excluding owner', function () {
    $member = User::factory()->create();
    $this->team->users()->attach($member->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->team->members)->toHaveCount(1);
    expect($this->team->members->first()->id)->toBe($member->id);
});

it('can check if user is owner', function () {
    $otherUser = User::factory()->create();

    expect($this->team->isOwner($this->user))->toBeTrue();
    expect($this->team->isOwner($otherUser))->toBeFalse();
});

it('can check if has user', function () {
    $member = User::factory()->create();
    $otherUser = User::factory()->create();

    expect($this->team->hasUser($member))->toBeFalse();

    $this->team->users()->attach($member->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->team->hasUser($member))->toBeTrue();
    expect($this->team->hasUser($otherUser))->toBeFalse();
});

it('can get user role', function () {
    $member = User::factory()->create();

    expect($this->team->userRole($member))->toBeNull();

    $this->team->users()->attach($member->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->team->userRole($member))->toBe('admin');
});

it('can check if personal team', function () {
    $personalTeam = Team::factory()->create(['type' => 'personal']);
    $companyTeam = Team::factory()->create(['type' => 'company']);

    expect($personalTeam->isPersonal())->toBeTrue();
    expect($companyTeam->isPersonal())->toBeFalse();
});

it('can check if company team', function () {
    $personalTeam = Team::factory()->create(['type' => 'personal']);
    $companyTeam = Team::factory()->create(['type' => 'company']);

    expect($personalTeam->isCompany())->toBeFalse();
    expect($companyTeam->isCompany())->toBeTrue();
});

it('uses slug as route key', function () {
    expect($this->team->getRouteKeyName())->toBe('slug');
});

it('generates fallback logo when no logo set', function () {
    $logo = $this->team->getLogoAttribute(null);

    expect($logo)->toContain('ui-avatars.com');
    expect($logo)->toContain('name='.urlencode($this->team->name));
});

it('returns storage url for uploaded logo', function () {
    $this->team->update(['logo' => 'team-logos/test.jpg']);
    $logo = $this->team->getLogoAttribute('team-logos/test.jpg');

    expect($logo)->toContain('storage/team-logos/test.jpg');
});

it('returns full url for external logo', function () {
    $externalUrl = 'https://example.com/logo.jpg';
    $this->team->update(['logo' => $externalUrl]);
    $logo = $this->team->getLogoAttribute($externalUrl);

    expect($logo)->toBe($externalUrl);
});

it('can get active plan with subscription', function () {
    $plan = Plan::factory()->create([
        'stripe_monthly_price_id' => 'price_monthly',
        'stripe_yearly_price_id' => 'price_yearly',
    ]);

    // Mock subscription
    $subscription = $this->team->subscriptions()->create([
        'name' => 'default',
        'stripe_id' => 'sub_test',
        'stripe_status' => 'active',
        'stripe_price' => 'price_monthly',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => null,
    ]);

    $activePlan = $this->team->getActivePlan();

    expect($activePlan['plan'])->toBeInstanceOf(Plan::class);
    expect($activePlan['plan']->id)->toBe($plan->id);
    expect($activePlan['cycle'])->toBe('monthly');
    expect($activePlan['subscription'])->not->toBeNull();
});

it('returns null plan when no active subscription', function () {
    $activePlan = $this->team->getActivePlan();

    expect($activePlan['plan'])->toBeNull();
    expect($activePlan['cycle'])->toBeNull();
    expect($activePlan['subscription'])->toBeNull();
});

it('returns null plan when subscription exists but plan not found', function () {
    $this->team->subscriptions()->create([
        'name' => 'default',
        'stripe_id' => 'sub_test',
        'stripe_status' => 'active',
        'stripe_price' => 'price_unknown',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => null,
    ]);

    $activePlan = $this->team->getActivePlan();

    expect($activePlan['plan'])->toBeNull();
    expect($activePlan['cycle'])->toBeNull();
    expect($activePlan['subscription'])->not->toBeNull();
});

it('can get active plan with yearly subscription', function () {
    $plan = Plan::factory()->create([
        'stripe_monthly_price_id' => 'price_monthly',
        'stripe_yearly_price_id' => 'price_yearly',
    ]);

    $this->team->subscriptions()->create([
        'name' => 'default',
        'stripe_id' => 'sub_test',
        'stripe_status' => 'active',
        'stripe_price' => 'price_yearly',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => null,
    ]);

    $activePlan = $this->team->getActivePlan();

    expect($activePlan['plan'])->toBeInstanceOf(Plan::class);
    expect($activePlan['cycle'])->toBe('yearly');
});

it('can get active plan with lifetime subscription', function () {
    $plan = Plan::factory()->create([
        'stripe_lifetime_price_id' => 'price_lifetime',
    ]);

    $this->team->subscriptions()->create([
        'name' => 'default',
        'stripe_id' => 'sub_test',
        'stripe_status' => 'active',
        'stripe_price' => 'price_lifetime',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => null,
    ]);

    $activePlan = $this->team->getActivePlan();

    expect($activePlan['plan'])->toBeInstanceOf(Plan::class);
    expect($activePlan['cycle'])->toBe('lifetime');
});
