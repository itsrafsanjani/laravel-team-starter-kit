<?php

use App\Actions\Auth\AuthenticateUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->action = new AuthenticateUser;
});

it('can authenticate user with valid credentials', function () {
    $request = Request::create('/login', 'POST', [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $request->setLaravelSession($this->app['session.store']);

    $result = $this->action->execute($request);

    expect($result)->toBeInstanceOf(User::class);
    expect($result->id)->toBe($this->user->id);
    $this->assertAuthenticated();
});

it('throws exception for invalid credentials', function () {
    $request = Request::create('/login', 'POST', [
        'email' => $this->user->email,
        'password' => 'wrong-password',
    ]);

    $request->setLaravelSession($this->app['session.store']);

    expect(fn () => $this->action->execute($request))
        ->toThrow(Exception::class);
});

it('throws exception when two factor authentication is required', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $this->user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $request = Request::create('/login', 'POST', [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $request->setLaravelSession($this->app['session.store']);

    expect(fn () => $this->action->execute($request))
        ->toThrow(Exception::class, 'Two-factor authentication required');
});

it('stores login session data for two factor authentication', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $this->user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $request = Request::create('/login', 'POST', [
        'email' => $this->user->email,
        'password' => 'password',
        'remember' => true,
    ]);

    $request->setLaravelSession($this->app['session.store']);

    try {
        $this->action->execute($request);
    } catch (Exception $e) {
        // Expected to throw exception for two factor
    }

    expect($this->app['session.store']->get('login.id'))->toBe($this->user->id);
    expect($this->app['session.store']->get('login.remember'))->toBeTrue();
});

it('regenerates session on successful authentication', function () {
    $request = Request::create('/login', 'POST', [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $request->setLaravelSession($this->app['session.store']);
    $oldSessionId = $this->app['session.store']->getId();

    $this->action->execute($request);

    $newSessionId = $this->app['session.store']->getId();
    expect($newSessionId)->not->toBe($oldSessionId);
});

it('logs in user with remember flag', function () {
    $request = Request::create('/login', 'POST', [
        'email' => $this->user->email,
        'password' => 'password',
        'remember' => true,
    ]);

    $request->setLaravelSession($this->app['session.store']);

    $this->action->execute($request);

    $this->assertAuthenticated();
    // Note: Testing remember functionality would require more complex setup
});

it('logs in user without remember flag', function () {
    $request = Request::create('/login', 'POST', [
        'email' => $this->user->email,
        'password' => 'password',
        'remember' => false,
    ]);

    $request->setLaravelSession($this->app['session.store']);

    $this->action->execute($request);

    $this->assertAuthenticated();
});
