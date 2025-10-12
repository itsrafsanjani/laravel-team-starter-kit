<?php

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    Event::fake();
});

it('can show registration page', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Auth/Register'));
});

it('can show registration page with invitation', function () {
    $team = Team::factory()->create();
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'test@example.com',
        'expires_at' => now()->addDays(7),
    ]);

    $response = $this->get(route('register', ['invitation_id' => $invitation->id]));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Auth/Register')
            ->has('invitation')
            ->where('prefilledEmail', 'test@example.com')
    );
});

it('does not show expired invitation', function () {
    $team = Team::factory()->create();
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'test@example.com',
        'expires_at' => now()->subDays(1),
    ]);

    $response = $this->get(route('register', ['invitation_id' => $invitation->id]));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Auth/Register')
            ->where('invitation', null)
            ->where('prefilledEmail', null)
    );
});

it('does not show accepted invitation', function () {
    $team = Team::factory()->create();
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'test@example.com',
        'accepted_at' => now(),
    ]);

    $response = $this->get(route('register', ['invitation_id' => $invitation->id]));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Auth/Register')
            ->where('invitation', null)
            ->where('prefilledEmail', null)
    );
});

it('can register new user', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('John Doe');
    expect(Hash::check('password', $user->password))->toBeTrue();

    $this->assertAuthenticated();
    $response->assertRedirect();

    Event::assertDispatched(Registered::class);
});

it('can register user with invitation', function () {
    $team = Team::factory()->create();
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'john@example.com',
        'expires_at' => now()->addDays(7),
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invitation_id' => $invitation->id,
    ]);

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();

    $this->assertAuthenticated();
    $response->assertRedirect(route('team.dashboard', $team->slug));
    $response->assertSessionHas('success');

    Event::assertDispatched(Registered::class);
});

it('validates required fields', function () {
    $response = $this->post(route('register.store'), []);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
});

it('validates email format', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('validates password confirmation', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors(['password']);
});

it('validates unique email', function () {
    User::factory()->create(['email' => 'john@example.com']);

    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('validates password strength', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertSessionHasErrors(['password']);
});

it('redirects to intended route after registration', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'john@example.com')->first();
    $team = $user->getDefaultTeam();

    expect($response->headers->get('Location'))->toContain(route('team.dashboard', $team->slug, absolute: false));
});
