<?php

use App\Models\Team;
use App\Models\User;
use App\Policies\TeamPolicy;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = Team::factory()->create();
    $this->policy = new TeamPolicy;
});

it('allows owner to view team', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->view($this->user, $team))->toBeTrue();
});

it('allows team members to view team', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->policy->view($this->user, $this->team))->toBeTrue();
});

it('denies access to non-members', function () {
    expect($this->policy->view($this->user, $this->team))->toBeFalse();
});

it('allows owner to update team', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->update($this->user, $team))->toBeTrue();
});

it('allows admin to update team', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->policy->update($this->user, $this->team))->toBeTrue();
});

it('denies update to regular members', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->policy->update($this->user, $this->team))->toBeFalse();
});

it('allows owner to delete team', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->delete($this->user, $team))->toBeTrue();
});

it('denies delete to admin', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->policy->delete($this->user, $this->team))->toBeFalse();
});

it('denies delete to members', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->policy->delete($this->user, $this->team))->toBeFalse();
});

it('allows owner to manage members', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->manageMembers($this->user, $team))->toBeTrue();
});

it('allows admin to manage members', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->policy->manageMembers($this->user, $this->team))->toBeTrue();
});

it('denies member management to regular members', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->policy->manageMembers($this->user, $this->team))->toBeFalse();
});

it('allows owner to view billing', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->viewBilling($this->user, $team))->toBeTrue();
});

it('allows admin to view billing', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->policy->viewBilling($this->user, $this->team))->toBeTrue();
});

it('denies billing view to regular members', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->policy->viewBilling($this->user, $this->team))->toBeFalse();
});

it('allows owner to manage billing', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->manageBilling($this->user, $team))->toBeTrue();
});

it('allows admin to manage billing', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->policy->manageBilling($this->user, $this->team))->toBeTrue();
});

it('denies billing management to regular members', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->policy->manageBilling($this->user, $this->team))->toBeFalse();
});

it('allows owner to invite members', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->inviteMembers($this->user, $team))->toBeTrue();
});

it('allows admin to invite members', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->policy->inviteMembers($this->user, $this->team))->toBeTrue();
});

it('denies member invitation to regular members', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->policy->inviteMembers($this->user, $this->team))->toBeFalse();
});

it('allows owner to remove members', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->removeMembers($this->user, $team))->toBeTrue();
});

it('allows admin to remove members', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->policy->removeMembers($this->user, $this->team))->toBeTrue();
});

it('denies member removal to regular members', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->policy->removeMembers($this->user, $this->team))->toBeFalse();
});

it('allows owner to view settings', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->viewSettings($this->user, $team))->toBeTrue();
});

it('allows admin to view settings', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->policy->viewSettings($this->user, $this->team))->toBeTrue();
});

it('denies settings view to regular members', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->policy->viewSettings($this->user, $this->team))->toBeFalse();
});

it('allows owner to update settings', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->policy->updateSettings($this->user, $team))->toBeTrue();
});

it('allows admin to update settings', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->policy->updateSettings($this->user, $this->team))->toBeTrue();
});

it('denies settings update to regular members', function () {
    $this->team->users()->attach($this->user->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->policy->updateSettings($this->user, $this->team))->toBeFalse();
});

it('denies all access to non-members', function () {
    expect($this->policy->view($this->user, $this->team))->toBeFalse();
    expect($this->policy->update($this->user, $this->team))->toBeFalse();
    expect($this->policy->delete($this->user, $this->team))->toBeFalse();
    expect($this->policy->manageMembers($this->user, $this->team))->toBeFalse();
    expect($this->policy->viewBilling($this->user, $this->team))->toBeFalse();
    expect($this->policy->manageBilling($this->user, $this->team))->toBeFalse();
    expect($this->policy->inviteMembers($this->user, $this->team))->toBeFalse();
    expect($this->policy->removeMembers($this->user, $this->team))->toBeFalse();
    expect($this->policy->viewSettings($this->user, $this->team))->toBeFalse();
    expect($this->policy->updateSettings($this->user, $this->team))->toBeFalse();
});
