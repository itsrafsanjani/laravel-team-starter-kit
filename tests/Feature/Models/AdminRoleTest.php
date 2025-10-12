<?php

use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminRole = AdminRole::factory()->create();
});

it('can be created with factory', function () {
    expect($this->adminRole)->toBeInstanceOf(AdminRole::class);
    expect($this->adminRole->name)->not->toBeEmpty();
    expect($this->adminRole->slug)->not->toBeEmpty();
});

it('has correct fillable attributes', function () {
    $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_active',
    ];

    expect($this->adminRole->getFillable())->toBe($fillable);
});

it('has correct casts', function () {
    expect($this->adminRole->getCasts())->toHaveKey('permissions', 'array');
    expect($this->adminRole->getCasts())->toHaveKey('is_active', 'boolean');
});

it('can have users', function () {
    $user = User::factory()->create();
    $this->adminRole->users()->attach($user->id, [
        'is_active' => true,
        'assigned_at' => now(),
    ]);

    expect($this->adminRole->users)->toHaveCount(1);
    expect($this->adminRole->users->first())->toBeInstanceOf(User::class);
    expect($this->adminRole->users->first()->id)->toBe($user->id);
});

it('can scope active roles', function () {
    $activeRole = AdminRole::factory()->create(['is_active' => true]);
    $inactiveRole = AdminRole::factory()->create(['is_active' => false]);

    $activeRoles = AdminRole::active()->get();

    expect($activeRoles)->toHaveCount(2); // $this->adminRole (from beforeEach) + $activeRole
    expect($activeRoles->pluck('id')->toArray())->toContain($activeRole->id);
    expect($activeRoles->pluck('id')->toArray())->toContain($this->adminRole->id);
});

it('can check if has permission', function () {
    $this->adminRole->update(['permissions' => ['manage_users', 'view_analytics']]);

    expect($this->adminRole->hasPermission('manage_users'))->toBeTrue();
    expect($this->adminRole->hasPermission('view_analytics'))->toBeTrue();
    expect($this->adminRole->hasPermission('manage_teams'))->toBeFalse();
});

it('can check admin panel access', function () {
    $this->adminRole->update([
        'is_active' => true,
        'permissions' => ['access_admin_panel'],
    ]);

    expect($this->adminRole->canAccessAdminPanel())->toBeTrue();

    $this->adminRole->update(['is_active' => false]);
    expect($this->adminRole->canAccessAdminPanel())->toBeFalse();

    $this->adminRole->update([
        'is_active' => true,
        'permissions' => ['manage_users'],
    ]);
    expect($this->adminRole->canAccessAdminPanel())->toBeFalse();
});

it('can check analytics permission', function () {
    $this->adminRole->update(['permissions' => ['view_analytics']]);

    expect($this->adminRole->canViewAnalytics())->toBeTrue();

    $this->adminRole->update(['permissions' => ['manage_users']]);
    expect($this->adminRole->canViewAnalytics())->toBeFalse();
});

it('can check users management permission', function () {
    $this->adminRole->update(['permissions' => ['manage_users']]);

    expect($this->adminRole->canManageUsers())->toBeTrue();

    $this->adminRole->update(['permissions' => ['view_analytics']]);
    expect($this->adminRole->canManageUsers())->toBeFalse();
});

it('can check teams management permission', function () {
    $this->adminRole->update(['permissions' => ['manage_teams']]);

    expect($this->adminRole->canManageTeams())->toBeTrue();

    $this->adminRole->update(['permissions' => ['view_analytics']]);
    expect($this->adminRole->canManageTeams())->toBeFalse();
});

it('can check plans management permission', function () {
    $this->adminRole->update(['permissions' => ['manage_plans']]);

    expect($this->adminRole->canManagePlans())->toBeTrue();

    $this->adminRole->update(['permissions' => ['view_analytics']]);
    expect($this->adminRole->canManagePlans())->toBeFalse();
});

it('can check reports permission', function () {
    $this->adminRole->update(['permissions' => ['view_reports']]);

    expect($this->adminRole->canViewReports())->toBeTrue();

    $this->adminRole->update(['permissions' => ['view_analytics']]);
    expect($this->adminRole->canViewReports())->toBeFalse();
});

it('handles null permissions gracefully', function () {
    $this->adminRole->update(['permissions' => null]);

    expect($this->adminRole->hasPermission('any_permission'))->toBeFalse();
    expect($this->adminRole->canAccessAdminPanel())->toBeFalse();
    expect($this->adminRole->canViewAnalytics())->toBeFalse();
    expect($this->adminRole->canManageUsers())->toBeFalse();
    expect($this->adminRole->canManageTeams())->toBeFalse();
    expect($this->adminRole->canManagePlans())->toBeFalse();
    expect($this->adminRole->canViewReports())->toBeFalse();
});

it('handles empty permissions array', function () {
    $this->adminRole->update(['permissions' => []]);

    expect($this->adminRole->hasPermission('any_permission'))->toBeFalse();
    expect($this->adminRole->canAccessAdminPanel())->toBeFalse();
});
