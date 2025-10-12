<?php

use App\Models\AdminRole;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->adminUser = User::factory()->create();
    $this->adminRole = AdminRole::factory()->create([
        'is_active' => true,
        'permissions' => ['access_admin_panel'],
    ]);
    $this->adminUser->adminRole()->attach($this->adminRole->id, [
        'is_active' => true,
        'assigned_at' => now(),
    ]);
    $this->actingAs($this->adminUser);
});

it('can access admin dashboard', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
});

it('shows admin dashboard with stats', function () {
    // Create some test data
    User::factory()->count(3)->create();
    Team::factory()->count(2)->create();
    AdminRole::factory()->count(1)->create();

    $response = $this->get(route('admin.dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page->component('Admin/Dashboard')
            ->has('stats.total_users')
            ->has('stats.total_teams')
            ->has('stats.total_admin_roles')
            ->has('stats.active_admin_users')
            ->has('recent_users')
            ->has('recent_teams')
    );
});

it('calculates correct user stats', function () {
    User::factory()->count(5)->create();

    $response = $this->get(route('admin.dashboard'));

    $response->assertInertia(
        fn ($page) => $page->where('stats.total_users', 6) // 5 + 1 admin user
    );
});

it('calculates correct team stats', function () {
    Team::factory()->count(3)->create();

    $response = $this->get(route('admin.dashboard'));

    $response->assertInertia(
        fn ($page) => $page->where('stats.total_teams', 3)
    );
});

it('calculates correct admin role stats', function () {
    AdminRole::factory()->count(2)->create();

    $response = $this->get(route('admin.dashboard'));

    $response->assertInertia(
        fn ($page) => $page->where('stats.total_admin_roles', 3) // 2 + 1 existing
    );
});

it('calculates correct active admin users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $role = AdminRole::factory()->create();

    $user1->adminRole()->attach($role->id, [
        'is_active' => true,
        'assigned_at' => now(),
    ]);

    $user2->adminRole()->attach($role->id, [
        'is_active' => false,
        'assigned_at' => now(),
    ]);

    $response = $this->get(route('admin.dashboard'));

    $response->assertInertia(
        fn ($page) => $page->where('stats.active_admin_users', 2) // 1 + 1 admin user
    );
});

it('shows recent users', function () {
    User::factory()->count(3)->create();

    $response = $this->get(route('admin.dashboard'));

    $response->assertInertia(
        fn ($page) => $page->has('recent_users', 4) // 3 created + 1 from beforeEach
    );
});

it('shows recent teams', function () {
    Team::factory()->count(2)->create();

    $response = $this->get(route('admin.dashboard'));

    $response->assertInertia(
        fn ($page) => $page->has('recent_teams', 2)
    );
});

it('denies access to non-admin users', function () {
    $regularUser = User::factory()->create();
    $this->actingAs($regularUser);

    $response = $this->get(route('admin.dashboard'));

    $response->assertStatus(403);
});

it('denies access to users with inactive admin roles', function () {
    $this->adminUser->adminRole()->detach();
    $this->adminUser->adminRole()->attach($this->adminRole->id, [
        'is_active' => false,
        'assigned_at' => now(),
    ]);

    $response = $this->get(route('admin.dashboard'));

    $response->assertStatus(403);
});

it('denies access to users with expired admin roles', function () {
    $this->adminUser->adminRole()->detach();
    $this->adminUser->adminRole()->attach($this->adminRole->id, [
        'is_active' => true,
        'assigned_at' => now(),
        'expires_at' => now()->subDays(1),
    ]);

    $response = $this->get(route('admin.dashboard'));

    $response->assertStatus(403);
});

it('redirects unauthenticated users to login', function () {
    // Logout the user to test unauthenticated access
    $this->post(route('logout'));

    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});
