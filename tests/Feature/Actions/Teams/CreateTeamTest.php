<?php

use App\Actions\Teams\CreateTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->action = new CreateTeam;
});

it('can create a team', function () {
    $teamData = [
        'name' => 'Test Team',
        'type' => 'personal',
        'website' => 'https://example.com',
        'phone' => '+1234567890',
        'address' => '123 Main St',
        'city' => 'New York',
        'state' => 'NY',
        'postal_code' => '10001',
        'country' => 'US',
        'settings' => ['theme' => 'dark'],
    ];

    $team = $this->action->execute($this->user, $teamData);

    expect($team)->toBeInstanceOf(Team::class);
    expect($team->name)->toBe('Test Team');
    expect($team->type)->toBe('personal');
    expect($team->user_id)->toBe($this->user->id);
    expect($team->website)->toBe('https://example.com');
    expect($team->phone)->toBe('+1234567890');
    expect($team->address)->toBe('123 Main St');
    expect($team->city)->toBe('New York');
    expect($team->state)->toBe('NY');
    expect($team->postal_code)->toBe('10001');
    expect($team->country)->toBe('US');
    expect($team->settings)->toBe(['theme' => 'dark']);
});

it('generates slug from email when not provided', function () {
    $teamData = [
        'name' => 'Test Team',
    ];

    $team = $this->action->execute($this->user, $teamData);

    expect($team->slug)->not->toBeEmpty();
    expect($team->slug)->toContain(Str::slug(explode('@', $this->user->email)[0]));
});

it('generates unique slug when slug already exists', function () {
    Team::factory()->create(['slug' => 'test-slug']);

    $teamData = [
        'name' => 'Test Team',
    ];

    $team = $this->action->execute($this->user, $teamData);

    expect($team->slug)->not->toBe('test-slug');
    expect($team->slug)->toContain(Str::slug(explode('@', $this->user->email)[0]));
});

it('adds user as owner to team', function () {
    $teamData = [
        'name' => 'Test Team',
    ];

    $team = $this->action->execute($this->user, $teamData);

    expect($team->users()->where('user_id', $this->user->id)->exists())->toBeTrue();

    $membership = $team->users()->where('user_id', $this->user->id)->first();
    expect($membership->pivot->role)->toBe('owner');
    expect($membership->pivot->joined_at)->not->toBeNull();
});

it('uses default type when not provided', function () {
    $teamData = [
        'name' => 'Test Team',
    ];

    $team = $this->action->execute($this->user, $teamData);

    expect($team->type)->toBe('personal');
});

it('handles empty settings', function () {
    $teamData = [
        'name' => 'Test Team',
    ];

    $team = $this->action->execute($this->user, $teamData);

    expect($team->settings)->toBe([]);
});

it('handles null optional fields', function () {
    $teamData = [
        'name' => 'Test Team',
        'website' => null,
        'phone' => null,
        'address' => null,
        'city' => null,
        'state' => null,
        'postal_code' => null,
        'country' => null,
    ];

    $team = $this->action->execute($this->user, $teamData);

    expect($team->website)->toBeNull();
    expect($team->phone)->toBeNull();
    expect($team->address)->toBeNull();
    expect($team->city)->toBeNull();
    expect($team->state)->toBeNull();
    expect($team->postal_code)->toBeNull();
    expect($team->country)->toBeNull();
});

it('runs in database transaction', function () {
    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
        return $callback();
    });

    $teamData = [
        'name' => 'Test Team',
    ];

    $this->action->execute($this->user, $teamData);
});

it('generates fallback slug for empty email username', function () {
    $user = User::factory()->create(['email' => '@example.com']);

    $teamData = [
        'name' => 'Test Team',
    ];

    $team = $this->action->execute($user, $teamData);

    expect($team->slug)->toBe('user');
});

it('generates fallback slug for special characters in email', function () {
    $user = User::factory()->create(['email' => 'user+test@example.com']);

    $teamData = [
        'name' => 'Test Team',
    ];

    $team = $this->action->execute($user, $teamData);

    expect($team->slug)->toBe('usertest');
});
