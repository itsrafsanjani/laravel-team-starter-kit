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
        $name = $this->faker->words(2, true);
        $slug = str($name)->slug();

        return [
            'name' => ucwords($name),
            'slug' => $slug,
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(PlanType::cases()),
            'monthly_price' => $this->faker->randomFloat(2, 9.99, 99.99),
            'yearly_price' => $this->faker->randomFloat(2, 99.99, 999.99),
            'lifetime_price' => $this->faker->randomFloat(2, 199.99, 1999.99),
            'stripe_monthly_price_id' => 'price_'.$this->faker->uuid(),
            'stripe_yearly_price_id' => 'price_'.$this->faker->uuid(),
            'stripe_lifetime_price_id' => 'price_'.$this->faker->uuid(),
            'trial_days' => $this->faker->numberBetween(0, 30),
            'features' => $this->faker->words($this->faker->numberBetween(3, 8)),
            'permissions' => [
                'max_team_members' => $this->faker->numberBetween(1, 100),
                'max_projects' => $this->faker->numberBetween(1, 50),
                'max_storage_gb' => $this->faker->numberBetween(1, 1000),
                'emails_per_month' => $this->faker->numberBetween(100, 100000),
                'api_calls_per_month' => $this->faker->numberBetween(1000, 1000000),
                'advanced_analytics' => $this->faker->boolean(),
                'api_access' => $this->faker->boolean(),
                'white_label' => $this->faker->boolean(),
                'priority_support' => $this->faker->boolean(),
            ],
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'is_popular' => $this->faker->boolean(20), // 20% chance of being popular
            'is_legacy' => $this->faker->boolean(10), // 10% chance of being legacy
            'sort_order' => $this->faker->numberBetween(0, 100),
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
            'trial_days' => $this->faker->numberBetween(7, 30),
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
