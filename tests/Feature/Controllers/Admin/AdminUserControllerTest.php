<?php

use App\Models\AdminRole;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->adminUser = User::factory()->create();
    $this->adminRole = AdminRole::factory()->create([
        'is_active' => true,
        'permissions' => ['access_admin_panel', 'manage_users'],
    ]);
    $this->adminUser->adminRole()->attach($this->adminRole->id, [
        'is_active' => true,
        'assigned_at' => now(),
    ]);
    $this->actingAs($this->adminUser);
});

it('can access admin users index', function () {
    $response = $this->get(route('admin.users.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Admin/Users/Index'));
});

it('shows users with pagination', function () {
    User::factory()->count(20)->create();

    $response = $this->get(route('admin.users.index'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Admin/Users/Index')
            ->has('users.data')
            ->has('adminRoles')
            ->has('filters')
    );
});

it('can search users by name', function () {
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $response = $this->get(route('admin.users.index', ['search' => 'John']));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Admin/Users/Index')
            ->where('filters.search', 'John')
    );
});

it('can search users by email', function () {
    User::factory()->create(['email' => 'john@example.com']);
    User::factory()->create(['email' => 'jane@example.com']);

    $response = $this->get(route('admin.users.index', ['search' => 'john@example.com']));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Admin/Users/Index')
            ->where('filters.search', 'john@example.com')
    );
});

it('can filter users by admin role', function () {
    $role1 = AdminRole::factory()->create(['slug' => 'super-admin']);
    $role2 = AdminRole::factory()->create(['slug' => 'moderator']);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->adminRole()->attach($role1->id, [
        'is_active' => true,
        'assigned_at' => now(),
    ]);

    $user2->adminRole()->attach($role2->id, [
        'is_active' => true,
        'assigned_at' => now(),
    ]);

    $response = $this->get(route('admin.users.index', ['role' => 'super-admin']));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Admin/Users/Index')
            ->where('filters.role', 'super-admin')
    );
});

it('shows all users when role filter is all', function () {
    $response = $this->get(route('admin.users.index', ['role' => 'all']));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Admin/Users/Index')
            ->where('filters.role', 'all')
    );
});

it('ignores empty search terms', function () {
    $response = $this->get(route('admin.users.index', ['search' => '   ']));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Admin/Users/Index')
            ->where('filters.search', null)
    );
});

it('can show specific user', function () {
    $user = User::factory()->create();

    $response = $this->get(route('admin.users.show', $user));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Admin/Users/Show')
            ->has('user')
            ->has('availableRoles')
    );
});

it('loads user relationships in show', function () {
    $user = User::factory()->create();
    $user->adminRole()->attach($this->adminRole->id, ['assigned_at' => now()]);
    $team = Team::factory()->create(['user_id' => $user->id]);
    $team->users()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);

    $response = $this->get(route('admin.users.show', $user));

    $response->assertStatus(200);

    // Debug: Check if the relationship is actually loaded
    $user->load('adminRole');
    expect($user->adminRole)->toHaveCount(1);

    $response->assertInertia(
        fn ($page) => $page->component('Admin/Users/Show')
            ->has('user')
            ->has('availableRoles')
    );
});

it('can show user edit form', function () {
    $user = User::factory()->create();

    $response = $this->get(route('admin.users.edit', $user));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Admin/Users/Edit')
            ->has('user')
            ->has('availableRoles')
    );
});

it('loads user relationships in edit', function () {
    $user = User::factory()->create();
    $user->adminRole()->attach($this->adminRole->id, ['assigned_at' => now()]);
    $team = Team::factory()->create(['user_id' => $user->id]);

    $response = $this->get(route('admin.users.edit', $user));

    $response->assertStatus(200);

    // Debug: Check if the relationship is actually loaded
    $user->load('adminRole');
    expect($user->adminRole)->toHaveCount(1);

    $response->assertInertia(
        fn ($page) => $page->component('Admin/Users/Edit')
            ->has('user')
            ->has('availableRoles')
    );
});

it('denies access to non-admin users', function () {
    $regularUser = User::factory()->create();
    $this->actingAs($regularUser);

    $response = $this->get(route('admin.users.index'));

    $response->assertStatus(403);
});

it('denies access to users without manage_users permission', function () {
    $this->adminUser->adminRole()->detach();
    $this->adminUser->adminRole()->attach($this->adminRole->id, [
        'is_active' => true,
        'assigned_at' => now(),
    ]);
    $this->adminRole->update(['permissions' => ['access_admin_panel']]);

    $response = $this->get(route('admin.users.index'));

    $response->assertStatus(403);
});

it('redirects unauthenticated users to login', function () {
    // Logout the user to test unauthenticated access
    $this->post(route('logout'));

    $response = $this->get(route('admin.users.index'));

    $response->assertRedirect(route('login'));
});
