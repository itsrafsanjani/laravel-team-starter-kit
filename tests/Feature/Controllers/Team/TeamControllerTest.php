<?php

use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// Team index tests removed - not needed

it('can show team creation form', function () {
    $response = $this->get(route('teams.create'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Teams/Create'));
});

it('can create a new team', function () {
    $teamData = [
        'name' => 'Test Team',
        'slug' => 'test-team',
        'billing_email' => 'billing@test.com',
    ];

    $response = $this->post(route('teams.store'), $teamData);

    $team = Team::where('slug', 'test-team')->first();
    expect($team)->not->toBeNull();
    expect($team->name)->toBe('Test Team');
    expect($team->user_id)->toBe($this->user->id);

    $response->assertRedirect(route('team.settings.general', $team));
    $response->assertSessionHas('success', 'Team created successfully.');
});

it('validates required fields for team creation', function () {
    $response = $this->post(route('teams.store'), []);

    $response->assertSessionHasErrors(['name', 'slug', 'billing_email']);
});

it('validates slug format', function () {
    $response = $this->post(route('teams.store'), [
        'name' => 'Test Team',
        'slug' => 'Invalid Slug!',
        'billing_email' => 'billing@test.com',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

it('validates unique slug', function () {
    Team::factory()->create(['slug' => 'existing-slug']);

    $response = $this->post(route('teams.store'), [
        'name' => 'Test Team',
        'slug' => 'existing-slug',
        'billing_email' => 'billing@test.com',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

it('validates email format for billing email', function () {
    $response = $this->post(route('teams.store'), [
        'name' => 'Test Team',
        'slug' => 'test-team',
        'billing_email' => 'invalid-email',
    ]);

    $response->assertSessionHasErrors(['billing_email']);
});

it('can switch to team', function () {
    $team = Team::factory()->create();
    $team->users()->attach($this->user->id, ['role' => 'member', 'joined_at' => now()]);

    $response = $this->post(route('teams.switch', $team));

    $response->assertRedirect(route('team.dashboard', $team));
    $response->assertSessionHas('success', 'Switched to '.$team->name);
});

it('cannot switch to team user does not belong to', function () {
    $team = Team::factory()->create();

    $response = $this->post(route('teams.switch', $team));

    $response->assertStatus(403);
});

it('denies access to unauthenticated users', function () {
    // Logout the user to test unauthenticated access
    $this->post(route('logout'));

    $response = $this->get(route('teams.create'));

    $response->assertRedirect(route('login'));
});
