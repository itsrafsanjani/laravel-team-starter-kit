<?php

use App\Models\Team;
use App\Models\TeamInvitation;

beforeEach(function () {
    $this->team = Team::factory()->create();
    $this->invitation = TeamInvitation::factory()->create(['team_id' => $this->team->id]);
});

it('can be created with factory', function () {
    expect($this->invitation)->toBeInstanceOf(TeamInvitation::class);
    expect($this->invitation->email)->not->toBeEmpty();
    expect($this->invitation->role)->not->toBeEmpty();
});

it('has correct fillable attributes', function () {
    $fillable = [
        'team_id',
        'email',
        'role',
        'accepted_at',
        'expires_at',
    ];

    expect($this->invitation->getFillable())->toBe($fillable);
});

it('has correct casts', function () {
    expect($this->invitation->getCasts())->toHaveKey('accepted_at', 'datetime');
    expect($this->invitation->getCasts())->toHaveKey('expires_at', 'datetime');
});

it('belongs to team', function () {
    expect($this->invitation->team)->toBeInstanceOf(Team::class);
    expect($this->invitation->team->id)->toBe($this->team->id);
});

it('can check if expired', function () {
    $expiredInvitation = TeamInvitation::factory()->create([
        'team_id' => $this->team->id,
        'expires_at' => now()->subDays(1),
    ]);

    $validInvitation = TeamInvitation::factory()->create([
        'team_id' => $this->team->id,
        'expires_at' => now()->addDays(1),
    ]);

    $noExpiryInvitation = TeamInvitation::factory()->create([
        'team_id' => $this->team->id,
        'expires_at' => null,
    ]);

    expect($expiredInvitation->isExpired())->toBeTrue();
    expect($validInvitation->isExpired())->toBeFalse();
    expect($noExpiryInvitation->isExpired())->toBeFalse();
});

it('can check if accepted', function () {
    $acceptedInvitation = TeamInvitation::factory()->create([
        'team_id' => $this->team->id,
        'accepted_at' => now(),
    ]);

    $pendingInvitation = TeamInvitation::factory()->create([
        'team_id' => $this->team->id,
        'accepted_at' => null,
    ]);

    expect($acceptedInvitation->isAccepted())->toBeTrue();
    expect($pendingInvitation->isAccepted())->toBeFalse();
});

it('can check if pending', function () {
    $acceptedInvitation = TeamInvitation::factory()->create([
        'team_id' => $this->team->id,
        'accepted_at' => now(),
    ]);

    $expiredInvitation = TeamInvitation::factory()->create([
        'team_id' => $this->team->id,
        'expires_at' => now()->subDays(1),
    ]);

    $pendingInvitation = TeamInvitation::factory()->create([
        'team_id' => $this->team->id,
        'accepted_at' => null,
        'expires_at' => now()->addDays(1),
    ]);

    expect($acceptedInvitation->isPending())->toBeFalse();
    expect($expiredInvitation->isPending())->toBeFalse();
    expect($pendingInvitation->isPending())->toBeTrue();
});

it('can be accepted', function () {
    expect($this->invitation->isAccepted())->toBeFalse();
    expect($this->invitation->isPending())->toBeTrue();

    $this->invitation->update(['accepted_at' => now()]);

    expect($this->invitation->isAccepted())->toBeTrue();
    expect($this->invitation->isPending())->toBeFalse();
});

it('becomes expired when expires_at is in the past', function () {
    $this->invitation->update(['expires_at' => now()->subMinutes(1)]);

    expect($this->invitation->isExpired())->toBeTrue();
    expect($this->invitation->isPending())->toBeFalse();
});

it('remains pending when expires_at is in the future', function () {
    $this->invitation->update(['expires_at' => now()->addDays(1)]);

    expect($this->invitation->isExpired())->toBeFalse();
    expect($this->invitation->isPending())->toBeTrue();
});

it('remains pending when no expiry date set', function () {
    $this->invitation->update(['expires_at' => null]);

    expect($this->invitation->isExpired())->toBeFalse();
    expect($this->invitation->isPending())->toBeTrue();
});
