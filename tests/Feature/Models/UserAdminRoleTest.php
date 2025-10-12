<?php

use App\Models\AdminRole;
use App\Models\User;
use App\Models\UserAdminRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->adminRole = AdminRole::factory()->create();
    $this->userAdminRole = UserAdminRole::factory()->active()->create([
        'user_id' => $this->user->id,
        'admin_role_id' => $this->adminRole->id,
    ]);
});

it('can be created with factory', function () {
    expect($this->userAdminRole)->toBeInstanceOf(UserAdminRole::class);
    expect($this->userAdminRole->user_id)->toBe($this->user->id);
    expect($this->userAdminRole->admin_role_id)->toBe($this->adminRole->id);
});

it('has correct fillable attributes', function () {
    $fillable = [
        'user_id',
        'admin_role_id',
        'is_active',
        'assigned_at',
        'expires_at',
    ];

    expect($this->userAdminRole->getFillable())->toBe($fillable);
});

it('has correct casts', function () {
    expect($this->userAdminRole->getCasts())->toHaveKey('is_active', 'boolean');
    expect($this->userAdminRole->getCasts())->toHaveKey('assigned_at', 'datetime');
    expect($this->userAdminRole->getCasts())->toHaveKey('expires_at', 'datetime');
});

it('belongs to user', function () {
    expect($this->userAdminRole->user)->toBeInstanceOf(User::class);
    expect($this->userAdminRole->user->id)->toBe($this->user->id);
});

it('belongs to admin role', function () {
    expect($this->userAdminRole->adminRole)->toBeInstanceOf(AdminRole::class);
    expect($this->userAdminRole->adminRole->id)->toBe($this->adminRole->id);
});

it('can check if active', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $user4 = User::factory()->create();

    $activeRole = UserAdminRole::factory()->create([
        'user_id' => $user1->id,
        'admin_role_id' => $this->adminRole->id,
        'is_active' => true,
        'expires_at' => now()->addDays(30),
    ]);

    $inactiveRole = UserAdminRole::factory()->create([
        'user_id' => $user2->id,
        'admin_role_id' => $this->adminRole->id,
        'is_active' => false,
    ]);

    $expiredRole = UserAdminRole::factory()->create([
        'user_id' => $user3->id,
        'admin_role_id' => $this->adminRole->id,
        'is_active' => true,
        'expires_at' => now()->subDays(1),
    ]);

    $noExpiryRole = UserAdminRole::factory()->create([
        'user_id' => $user4->id,
        'admin_role_id' => $this->adminRole->id,
        'is_active' => true,
        'expires_at' => null,
    ]);

    expect($activeRole->isActive())->toBeTrue();
    expect($inactiveRole->isActive())->toBeFalse();
    expect($expiredRole->isActive())->toBeFalse();
    expect($noExpiryRole->isActive())->toBeTrue();
});

it('can check if expired', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $expiredRole = UserAdminRole::factory()->create([
        'user_id' => $user1->id,
        'admin_role_id' => $this->adminRole->id,
        'expires_at' => now()->subDays(1),
    ]);

    $activeRole = UserAdminRole::factory()->create([
        'user_id' => $user2->id,
        'admin_role_id' => $this->adminRole->id,
        'expires_at' => now()->addDays(30),
    ]);

    $noExpiryRole = UserAdminRole::factory()->create([
        'user_id' => $user3->id,
        'admin_role_id' => $this->adminRole->id,
        'expires_at' => null,
    ]);

    expect($expiredRole->isExpired())->toBeTrue();
    expect($activeRole->isExpired())->toBeFalse();
    expect($noExpiryRole->isExpired())->toBeFalse();
});

it('can scope active roles', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $user4 = User::factory()->create();

    $activeRole = UserAdminRole::factory()->create([
        'user_id' => $user1->id,
        'admin_role_id' => $this->adminRole->id,
        'is_active' => true,
        'expires_at' => now()->addDays(30),
    ]);

    $inactiveRole = UserAdminRole::factory()->create([
        'user_id' => $user2->id,
        'admin_role_id' => $this->adminRole->id,
        'is_active' => false,
    ]);

    $expiredRole = UserAdminRole::factory()->create([
        'user_id' => $user3->id,
        'admin_role_id' => $this->adminRole->id,
        'is_active' => true,
        'expires_at' => now()->subDays(1),
    ]);

    $noExpiryRole = UserAdminRole::factory()->create([
        'user_id' => $user4->id,
        'admin_role_id' => $this->adminRole->id,
        'is_active' => true,
        'expires_at' => null,
    ]);

    $activeRoles = UserAdminRole::active()->get();

    expect($activeRoles)->toHaveCount(3); // $this->userAdminRole (from beforeEach) + $activeRole + $noExpiryRole
    expect($activeRoles->pluck('id')->toArray())->toContain($activeRole->id);
    expect($activeRoles->pluck('id')->toArray())->toContain($noExpiryRole->id);
    expect($activeRoles->pluck('id')->toArray())->toContain($this->userAdminRole->id);
    expect($activeRoles->pluck('id')->toArray())->not->toContain($inactiveRole->id);
    expect($activeRoles->pluck('id')->toArray())->not->toContain($expiredRole->id);
});

it('can scope expired roles', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $expiredRole = UserAdminRole::factory()->create([
        'user_id' => $user1->id,
        'admin_role_id' => $this->adminRole->id,
        'expires_at' => now()->subDays(1),
    ]);

    $activeRole = UserAdminRole::factory()->create([
        'user_id' => $user2->id,
        'admin_role_id' => $this->adminRole->id,
        'expires_at' => now()->addDays(30),
    ]);

    $noExpiryRole = UserAdminRole::factory()->create([
        'user_id' => $user3->id,
        'admin_role_id' => $this->adminRole->id,
        'expires_at' => null,
    ]);

    $expiredRoles = UserAdminRole::expired()->get();

    expect($expiredRoles)->toHaveCount(1);
    expect($expiredRoles->first()->id)->toBe($expiredRole->id);
});
