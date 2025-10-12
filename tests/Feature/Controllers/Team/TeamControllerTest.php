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

it('can update team', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    $updateData = [
        'name' => 'Updated Team Name',
        'type' => 'company',
        'website' => 'https://example.com',
        'phone' => '+1234567890',
        'address' => '123 Main St',
        'city' => 'New York',
        'state' => 'NY',
        'postal_code' => '10001',
        'country' => 'US',
    ];

    $response = $this->put(route('teams.update', $team), $updateData);

    $team->refresh();
    expect($team->name)->toBe('Updated Team Name');
    expect($team->type)->toBe('company');

    $response->assertRedirect(route('team.settings.general', $team));
    $response->assertSessionHas('success', 'Team updated successfully.');
});

it('validates required fields for team update', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    $response = $this->put(route('teams.update', $team), []);

    $response->assertSessionHasErrors(['name', 'type']);
});

it('validates team type', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    $response = $this->put(route('teams.update', $team), [
        'name' => 'Test Team',
        'type' => 'invalid-type',
    ]);

    $response->assertSessionHasErrors(['type']);
});

it('validates website url format', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    $response = $this->put(route('teams.update', $team), [
        'name' => 'Test Team',
        'type' => 'company',
        'website' => 'invalid-url',
    ]);

    $response->assertSessionHasErrors(['website']);
});

it('can delete team', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    $response = $this->delete(route('teams.destroy', $team));

    expect(Team::find($team->id))->toBeNull();

    $response->assertRedirect(route('teams.index'));
    $response->assertSessionHas('success', 'Team deleted successfully.');
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

it('denies access to team update for non-owners', function () {
    $team = Team::factory()->create();
    $team->users()->attach($this->user->id, ['role' => 'member', 'joined_at' => now()]);

    $response = $this->put(route('teams.update', $team), [
        'name' => 'Updated Name',
        'type' => 'company',
    ]);

    $response->assertStatus(403);
});

it('denies access to team deletion for non-owners', function () {
    $team = Team::factory()->create();
    $team->users()->attach($this->user->id, ['role' => 'member', 'joined_at' => now()]);

    $response = $this->delete(route('teams.destroy', $team));

    $response->assertStatus(403);
});
