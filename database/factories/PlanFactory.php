<?php

namespace Database\Factories;

use App\Enums\PlanType;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);
        $slug = str($name)->slug();

        return [
            'name' => ucwords($name),
            'slug' => $slug,
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(PlanType::cases()),
            'monthly_price' => fake()->randomFloat(2, 9.99, 99.99),
            'yearly_price' => fake()->randomFloat(2, 99.99, 999.99),
            'lifetime_price' => fake()->randomFloat(2, 199.99, 1999.99),
            'stripe_monthly_price_id' => 'price_'.fake()->uuid(),
            'stripe_yearly_price_id' => 'price_'.fake()->uuid(),
            'stripe_lifetime_price_id' => 'price_'.fake()->uuid(),
            'trial_days' => fake()->numberBetween(0, 30),
            'features' => fake()->words(fake()->numberBetween(3, 8)),
            'permissions' => [
                'max_team_members' => fake()->numberBetween(1, 100),
                'max_projects' => fake()->numberBetween(1, 50),
                'max_storage_gb' => fake()->numberBetween(1, 1000),
                'emails_per_month' => fake()->numberBetween(100, 100000),
                'api_calls_per_month' => fake()->numberBetween(1000, 1000000),
                'advanced_analytics' => fake()->boolean(),
                'api_access' => fake()->boolean(),
                'white_label' => fake()->boolean(),
                'priority_support' => fake()->boolean(),
            ],
            'is_active' => fake()->boolean(80), // 80% chance of being active
            'is_popular' => fake()->boolean(20), // 20% chance of being popular
            'is_legacy' => fake()->boolean(10), // 10% chance of being legacy
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the plan is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PlanType::FREE,
            'monthly_price' => null,
            'yearly_price' => null,
            'lifetime_price' => null,
            'trial_days' => 0,
        ]);
    }

    /**
     * Indicate that the plan is a trial.
     */
    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PlanType::TRIAL,
            'monthly_price' => null,
            'yearly_price' => null,
            'lifetime_price' => null,
            'trial_days' => fake()->numberBetween(7, 30),
        ]);
    }

    /**
     * Indicate that the plan is a subscription.
     */
    public function subscription(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PlanType::SUBSCRIPTION,
            'lifetime_price' => null,
        ]);
    }

    /**
     * Indicate that the plan is lifetime.
     */
    public function lifetime(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PlanType::LIFETIME,
            'monthly_price' => null,
            'yearly_price' => null,
        ]);
    }

    /**
     * Indicate that the plan is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the plan is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the plan is popular.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_popular' => true,
        ]);
    }

    /**
     * Indicate that the plan is legacy.
     */
    public function legacy(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_legacy' => true,
        ]);
    }
}
