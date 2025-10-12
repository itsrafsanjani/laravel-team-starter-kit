<?php

use App\Models\AdminRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can be created with factory', function () {
    expect($this->user)->toBeInstanceOf(User::class);
    expect($this->user->name)->not->toBeEmpty();
    expect($this->user->email)->not->toBeEmpty();
});

it('has correct fillable attributes', function () {
    $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'avatar',
        'is_banned',
        'banned_reason',
    ];

    expect($this->user->getFillable())->toBe($fillable);
});

it('has correct hidden attributes', function () {
    $hidden = ['password', 'remember_token'];
    expect($this->user->getHidden())->toBe($hidden);
});

it('has correct casts', function () {
    expect($this->user->getCasts())->toHaveKey('email_verified_at', 'datetime');
    expect($this->user->getCasts())->toHaveKey('password', 'hashed');
    expect($this->user->getCasts())->toHaveKey('is_banned', 'boolean');
});

it('can have owned teams', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->user->ownedTeams)->toHaveCount(1);
    expect($this->user->ownedTeams->first())->toBeInstanceOf(Team::class);
    expect($this->user->ownedTeams->first()->id)->toBe($team->id);
});

it('can belong to teams', function () {
    $team = Team::factory()->create();
    $team->users()->attach($this->user->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->user->teams)->toHaveCount(1);
    expect($this->user->teams->first())->toBeInstanceOf(Team::class);
    expect($this->user->teams->first()->id)->toBe($team->id);
});

it('can have team invitations', function () {
    $team = Team::factory()->create();
    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => $this->user->email,
    ]);

    expect($this->user->teamInvitations)->toHaveCount(1);
    expect($this->user->teamInvitations->first())->toBeInstanceOf(TeamInvitation::class);
    expect($this->user->teamInvitations->first()->id)->toBe($invitation->id);
});

it('can check if belongs to team', function () {
    $team = Team::factory()->create();

    expect($this->user->belongsToTeam($team))->toBeFalse();

    $team->users()->attach($this->user->id, [
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect($this->user->belongsToTeam($team))->toBeTrue();
});

it('can check if owns team', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);
    $otherTeam = Team::factory()->create();

    expect($this->user->ownsTeam($team))->toBeTrue();
    expect($this->user->ownsTeam($otherTeam))->toBeFalse();
});

it('can get team role', function () {
    $team = Team::factory()->create();

    expect($this->user->teamRole($team))->toBeNull();

    $team->users()->attach($this->user->id, [
        'role' => 'admin',
        'joined_at' => now(),
    ]);

    expect($this->user->teamRole($team))->toBe('admin');
});

it('returns owner role for owned teams', function () {
    $team = Team::factory()->create(['user_id' => $this->user->id]);

    expect($this->user->teamRole($team))->toBe('owner');
});

it('can get default team', function () {
    // No teams initially
    expect($this->user->getDefaultTeam())->toBeNull();

    // Create a personal team
    $personalTeam = Team::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'personal',
    ]);

    expect($this->user->getDefaultTeam())->toBeInstanceOf(Team::class);
    expect($this->user->getDefaultTeam()->id)->toBe($personalTeam->id);

    // Create a company team
    $companyTeam = Team::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'company',
    ]);

    // Should still return personal team as default
    expect($this->user->getDefaultTeam()->id)->toBe($personalTeam->id);
});

it('can check if has any team', function () {
    expect($this->user->hasAnyTeam())->toBeFalse();

    $team = Team::factory()->create(['user_id' => $this->user->id]);
    expect($this->user->hasAnyTeam())->toBeTrue();
});

it('can have admin roles', function () {
    $adminRole = AdminRole::factory()->create();
    $this->user->adminRole()->attach($adminRole->id, [
        'is_active' => true,
        'assigned_at' => now(),
    ]);

    expect($this->user->adminRole)->toHaveCount(1);
    expect($this->user->adminRole->first())->toBeInstanceOf(AdminRole::class);
});

it('can check admin panel access', function () {
    expect($this->user->canAccessAdminPanel())->toBeFalse();

    $adminRole = AdminRole::factory()->create();
    $this->user->adminRole()->attach($adminRole->id, [
        'is_active' => true,
        'assigned_at' => now(),
    ]);

    expect($this->user->canAccessAdminPanel())->toBeTrue();
});

it('can check specific admin role', function () {
    $adminRole = AdminRole::factory()->create(['slug' => 'super-admin']);
    $this->user->adminRole()->attach($adminRole->id, [
        'is_active' => true,
        'assigned_at' => now(),
    ]);

    expect($this->user->hasAdminRole('super-admin'))->toBeTrue();
    expect($this->user->hasAdminRole('regular-admin'))->toBeFalse();
});

it('can check if banned', function () {
    expect($this->user->isBanned())->toBeFalse();

    $this->user->update(['is_banned' => true]);
    expect($this->user->isBanned())->toBeTrue();
});

it('can be banned with reason', function () {
    $this->user->ban('Spam account');

    expect($this->user->is_banned)->toBeTrue();
    expect($this->user->banned_reason)->toBe('Spam account');
});

it('can be unbanned', function () {
    $this->user->ban('Spam account');
    $this->user->unban();

    expect($this->user->is_banned)->toBeFalse();
    expect($this->user->banned_reason)->toBeNull();
});

it('generates gravatar avatar when no avatar set', function () {
    $this->user->update(['avatar' => null]);
    $avatar = $this->user->getAvatarAttribute();

    expect($avatar)->toContain('gravatar.com');
    expect($avatar)->toContain(md5(strtolower(trim($this->user->email))));
});

it('returns storage url when avatar is set', function () {
    Storage::fake('public');

    $this->user->update(['avatar' => 'avatars/test.jpg']);
    $avatar = $this->user->getAvatarAttribute();

    expect($avatar)->toContain('storage/avatars/test.jpg');
});

it('can have admin roles with proper filtering', function () {
    $user = User::factory()->create();
    $activeRole = AdminRole::factory()->create();

    // Test active role
    $user->adminRole()->attach($activeRole->id, [
        'is_active' => true,
        'assigned_at' => now(),
        'expires_at' => now()->addDays(30),
    ]);

    expect($user->adminRole)->toHaveCount(1);
    expect($user->adminRole->first()->id)->toBe($activeRole->id);
});
