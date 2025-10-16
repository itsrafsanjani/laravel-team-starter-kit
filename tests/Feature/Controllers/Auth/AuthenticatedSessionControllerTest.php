<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can show login page', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Auth/Login'));
});

it('can authenticate user with valid credentials', function () {
    $response = $this->post(route('login.store'), [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

it('cannot authenticate user with invalid credentials', function () {
    $response = $this->post(route('login.store'), [
        'email' => $this->user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

it('redirects to two factor challenge when two factor is enabled', function () {
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

    $response = $this->post(route('login.store'), [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHas('login.id', $this->user->id);
    $this->assertGuest();
});

it('can logout authenticated user', function () {
    $this->actingAs($this->user);

    // Verify user is authenticated before logout
    expect(Auth::check())->toBeTrue();

    $response = $this->post(route('logout'));

    // Check authentication state after logout
    expect(Auth::check())->toBeFalse();
    $this->assertGuest();
    $response->assertRedirect('/');
});

it('includes can reset password flag in login page', function () {
    $response = $this->get(route('login'));

    $response->assertInertia(fn ($page) => $page->has('canResetPassword'));
});

it('includes status in login page', function () {
    $this->withSession(['status' => 'test-status']);

    $response = $this->get(route('login'));

    $response->assertInertia(fn ($page) => $page->where('status', 'test-status'));
});

it('regenerates session on successful login', function () {
    $oldSessionId = session()->getId();

    $this->post(route('login.store'), [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $newSessionId = session()->getId();
    expect($newSessionId)->not->toBe($oldSessionId);
});

it('invalidates session on logout', function () {
    $this->actingAs($this->user);
    $oldSessionId = session()->getId();

    $this->post(route('logout'));

    $newSessionId = session()->getId();
    expect($newSessionId)->not->toBe($oldSessionId);
});

it('regenerates csrf token on logout', function () {
    $this->actingAs($this->user);
    $oldToken = csrf_token();

    $this->post(route('logout'));

    $newToken = csrf_token();
    expect($newToken)->not->toBe($oldToken);
});
