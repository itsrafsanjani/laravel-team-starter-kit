<?php

use App\Actions\Auth\RegisterUser;
use App\Actions\Teams\AcceptTeamInvitation;
use App\Actions\Teams\CreateTeam;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    Event::fake();
    $this->createTeam = new CreateTeam;
    $this->acceptTeamInvitation = new AcceptTeamInvitation;
    $this->action = new RegisterUser($this->createTeam, $this->acceptTeamInvitation);
});

it('can register new user', function () {
    $request = Request::create('/register', 'POST', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $result = $this->action->execute($request);

    expect($result)->toBeArray();
    expect($result['is_invitation'])->toBeFalse();
    expect($result['team'])->toBeInstanceOf(Team::class);
    expect($result)->toHaveKey('user');

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('John Doe');
    expect(Hash::check('password', $user->password))->toBeTrue();

    Event::assertDispatched(Registered::class);
});

it('can register user with invitation', function () {
    $team = Team::factory()->create();
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'john@example.com',
        'expires_at' => now()->addDays(7),
    ]);

    $request = Request::create('/register', 'POST', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invitation_id' => $invitation->id,
    ]);

    $result = $this->action->execute($request);

    expect($result)->toBeArray();
    expect($result['is_invitation'])->toBeTrue();
    expect($result['team'])->toBeInstanceOf(Team::class);
    expect($result['message'])->not->toBeEmpty();

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();

    Event::assertDispatched(Registered::class);
});

it('validates required fields', function () {
    $request = Request::create('/register', 'POST', []);

    expect(fn () => $this->action->execute($request))
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

it('validates email format', function () {
    $request = Request::create('/register', 'POST', [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(fn () => $this->action->execute($request))
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

it('validates password confirmation', function () {
    $request = Request::create('/register', 'POST', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'different-password',
    ]);

    expect(fn () => $this->action->execute($request))
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

it('validates unique email', function () {
    User::factory()->create(['email' => 'john@example.com']);

    $request = Request::create('/register', 'POST', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(fn () => $this->action->execute($request))
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

it('validates password strength', function () {
    $request = Request::create('/register', 'POST', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    expect(fn () => $this->action->execute($request))
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

it('creates personal team for new user', function () {
    $request = Request::create('/register', 'POST', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $result = $this->action->execute($request);

    $user = User::where('email', 'john@example.com')->first();
    $team = $user->ownedTeams()->where('type', 'personal')->first();

    expect($team)->not->toBeNull();
    expect($team->user_id)->toBe($user->id);
    expect($team->users()->where('user_id', $user->id)->exists())->toBeTrue();
});

it('handles invitation acceptance', function () {
    $team = Team::factory()->create();
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'john@example.com',
        'expires_at' => now()->addDays(7),
    ]);

    $request = Request::create('/register', 'POST', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invitation_id' => $invitation->id,
    ]);

    $result = $this->action->execute($request);

    $user = User::where('email', 'john@example.com')->first();
    expect($user->teams()->where('team_id', $team->id)->exists())->toBeTrue();
});

it('does not accept expired invitation', function () {
    $team = Team::factory()->create();
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'john@example.com',
        'expires_at' => now()->subDays(1),
    ]);

    $request = Request::create('/register', 'POST', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invitation_id' => $invitation->id,
    ]);

    $result = $this->action->execute($request);

    $user = User::where('email', 'john@example.com')->first();
    expect($user->teams()->where('team_id', $team->id)->exists())->toBeFalse();
});

it('does not accept already accepted invitation', function () {
    $team = Team::factory()->create();
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'john@example.com',
        'accepted_at' => now(),
    ]);

    $request = Request::create('/register', 'POST', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invitation_id' => $invitation->id,
    ]);

    $result = $this->action->execute($request);

    $user = User::where('email', 'john@example.com')->first();
    expect($user->teams()->where('team_id', $team->id)->exists())->toBeFalse();
});
