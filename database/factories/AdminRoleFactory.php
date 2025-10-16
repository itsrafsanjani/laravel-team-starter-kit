<?php

namespace Database\Factories;

use App\Models\AdminRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminRole>
 */
class AdminRoleFactory extends Factory
{
    protected $model = AdminRole::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->slug(),
            'description' => fake()->sentence(),
            'permissions' => ['access_admin_panel'],
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the role is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a super admin role.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access to all admin features',
            'permissions' => [
                'access_admin_panel',
                'view_analytics',
                'manage_users',
                'manage_teams',
                'manage_plans',
                'view_reports',
                'manage_billing',
                'manage_system',
            ],
        ]);
    }
}
