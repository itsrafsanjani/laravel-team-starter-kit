<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'type' => fake()->randomElement(['personal', 'company']),
            'website' => fake()->optional()->url(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->streetAddress(),
            'city' => fake()->optional()->city(),
            'state' => fake()->optional()->state(),
            'postal_code' => fake()->optional()->postcode(),
            'country' => fake()->optional()->country(),
            'settings' => [],
        ];
    }

    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'personal',
        ]);
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'company',
        ]);
    }
}
