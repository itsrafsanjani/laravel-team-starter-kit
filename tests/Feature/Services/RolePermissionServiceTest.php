<?php

use App\Models\Team;
use App\Models\User;
use App\Services\RolePermissionService;

beforeEach(function () {
    $this->service = new RolePermissionService;
    $this->user = User::factory()->create();
    $this->team = Team::factory()->create();
});

it('can get all available roles', function () {
    $roles = $this->service->getAllRoles();

    expect($roles)->toBeArray();
    expect($roles)->toHaveKey('owner');
    expect($roles)->toHaveKey('admin');
    expect($roles)->toHaveKey('member');
});

it('can get role permissions', function () {
    $ownerPermissions = $this->service->getRolePermissions('owner');
    $adminPermissions = $this->service->getRolePermissions('admin');
    $memberPermissions = $this->service->getRolePermissions('member');

    expect($ownerPermissions)->toBeArray();
    expect($adminPermissions)->toBeArray();
    expect($memberPermissions)->toBeArray();
});

it('can check if role has permission', function () {
    expect($this->service->roleHasPermission('owner', 'view_team'))->toBeTrue();
    expect($this->service->roleHasPermission('admin', 'view_team'))->toBeTrue();
    expect($this->service->roleHasPermission('member', 'view_team'))->toBeTrue();
    expect($this->service->roleHasPermission('member', 'delete_team'))->toBeFalse();
});

it('can check user team permission', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->service->userHasTeamPermission($this->user, $this->team, 'view_team'))->toBeTrue();
    expect($this->service->userHasTeamPermission($this->user, $this->team, 'delete_team'))->toBeFalse();
});

it('returns false for user not in team', function () {
    expect($this->service->userHasTeamPermission($this->user, $this->team, 'view_team'))->toBeFalse();
});

it('can get user team role info', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    $roleInfo = $this->service->getUserTeamRoleInfo($this->user, $this->team);

    expect($roleInfo)->toBeArray();
    expect($roleInfo)->toHaveKey('name');
    expect($roleInfo)->toHaveKey('permissions');
});

it('returns null for user not in team', function () {
    $roleInfo = $this->service->getUserTeamRoleInfo($this->user, $this->team);

    expect($roleInfo)->toBeNull();
});

it('can get user team permissions', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    $permissions = $this->service->getUserTeamPermissions($this->user, $this->team);

    expect($permissions)->toBeArray();
    expect($permissions)->toContain('view_team');
});

it('returns empty array for user not in team', function () {
    $permissions = $this->service->getUserTeamPermissions($this->user, $this->team);

    expect($permissions)->toBeArray();
    expect($permissions)->toBeEmpty();
});

it('can check if user can perform action', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->service->can($this->user, $this->team, 'view_team'))->toBeTrue();
    expect($this->service->can($this->user, $this->team, 'delete_team'))->toBeFalse();
});

it('can get all permissions', function () {
    $permissions = $this->service->getAllPermissions();

    expect($permissions)->toBeArray();
    expect($permissions)->toHaveKey('view_team');
    expect($permissions)->toHaveKey('delete_team');
});

it('can get role hierarchy', function () {
    $hierarchy = $this->service->getRoleHierarchy();

    expect($hierarchy)->toBeArray();
    expect($hierarchy)->toHaveKey('owner', 3);
    expect($hierarchy)->toHaveKey('admin', 2);
    expect($hierarchy)->toHaveKey('member', 1);
});

it('can check if one role is higher than another', function () {
    expect($this->service->isRoleHigher('owner', 'admin'))->toBeTrue();
    expect($this->service->isRoleHigher('admin', 'member'))->toBeTrue();
    expect($this->service->isRoleHigher('member', 'admin'))->toBeFalse();
    expect($this->service->isRoleHigher('owner', 'owner'))->toBeFalse();
});

it('handles unknown roles in hierarchy comparison', function () {
    expect($this->service->isRoleHigher('unknown', 'admin'))->toBeFalse();
    expect($this->service->isRoleHigher('admin', 'unknown'))->toBeTrue();
});

it('works with owner role', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->service->userHasTeamPermission($this->user, $team, 'view_team'))->toBeTrue();
    expect($this->service->userHasTeamPermission($this->user, $team, 'delete_team'))->toBeTrue();

    $roleInfo = $this->service->getUserTeamRoleInfo($this->user, $team);
    expect($roleInfo)->not->toBeNull();

    $permissions = $this->service->getUserTeamPermissions($this->user, $team);
    expect($permissions)->toContain('view_team');
    expect($permissions)->toContain('delete_team');
});
