<?php

namespace Database\Factories;

use App\Models\AdminRole;
use App\Models\User;
use App\Models\UserAdminRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserAdminRole>
 */
class UserAdminRoleFactory extends Factory
{
    protected $model = UserAdminRole::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'admin_role_id' => AdminRole::factory(),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'assigned_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'expires_at' => $this->faker->optional(0.3)->dateTimeBetween('now', '+1 year'),
        ];
    }

    /**
     * Indicate that the role is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'expires_at' => null,
        ]);
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
     * Indicate that the role is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'expires_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }
}
