<?php

namespace Database\Seeders;

use App\Enums\PlanType;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Free Plan
        $freePlan = Plan::firstOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for getting started',
                'type' => PlanType::FREE,
                'monthly_price' => null,
                'yearly_price' => null,
                'lifetime_price' => null,
                'trial_days' => 0,
                'features' => [
                    'Basic analytics',
                    'Email support',
                    'Standard templates',
                ],
                'permissions' => [
                    'max_team_members' => 2,
                    'max_projects' => 3,
                    'max_storage_gb' => 1,
                    'emails_per_month' => 1000,
                    'api_calls_per_month' => 10000,
                    'advanced_analytics' => false,
                    'api_access' => false,
                    'white_label' => false,
                    'priority_support' => false,
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 1,
            ]
        );

        // Hobby Plan
        $hobbyPlan = Plan::firstOrCreate(
            ['slug' => 'hobby'],
            [
                'name' => 'Hobby',
                'slug' => 'hobby',
                'description' => 'Perfect for individuals and small projects',
                'type' => PlanType::SUBSCRIPTION,
                'monthly_price' => 19.00,
                'yearly_price' => 190.00,
                'lifetime_price' => null,
                'trial_days' => 14,
                'stripe_monthly_price_id' => 'price_1SCk2a2FUElA6MJh3GNoH16v',
                'stripe_yearly_price_id' => 'price_1SCk322FUElA6MJhffSVUkZS',
                'features' => [
                    'Advanced analytics',
                    'Email support',
                    'Custom templates',
                    'API access',
                ],
                'permissions' => [
                    'max_team_members' => 3,
                    'max_projects' => 5,
                    'max_storage_gb' => 5,
                    'emails_per_month' => 5000,
                    'api_calls_per_month' => 50000,
                    'advanced_analytics' => true,
                    'api_access' => true,
                    'white_label' => false,
                    'priority_support' => false,
                    'custom_integrations' => false,
                ],
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 2,
            ]
        );

        // Growth Plan
        $growthPlan = Plan::firstOrCreate(
            ['slug' => 'growth'],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'Great for growing teams and businesses',
                'type' => PlanType::SUBSCRIPTION,
                'monthly_price' => 49.00,
                'yearly_price' => 490.00,
                'lifetime_price' => null,
                'trial_days' => 14,
                'stripe_monthly_price_id' => 'price_1SCk3P2FUElA6MJhtAKsI1X5',
                'stripe_yearly_price_id' => 'price_1SCk3e2FUElA6MJhZVu3UnJq',
                'features' => [
                    'Advanced analytics',
                    'Priority support',
                    'Custom templates',
                    'API access',
                    'White-label options',
                    'Custom integrations',
                ],
                'permissions' => [
                    'max_team_members' => 10,
                    'max_projects' => 25,
                    'max_storage_gb' => 25,
                    'emails_per_month' => 25000,
                    'api_calls_per_month' => 250000,
                    'advanced_analytics' => true,
                    'api_access' => true,
                    'white_label' => true,
                    'priority_support' => true,
                    'custom_integrations' => true,
                    'sso_integration' => false,
                    'advanced_security' => false,
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 3,
            ]
        );

        // Business Plan
        $businessPlan = Plan::firstOrCreate(
            ['slug' => 'business'],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'For established businesses and enterprises',
                'type' => PlanType::SUBSCRIPTION,
                'monthly_price' => 99.00,
                'yearly_price' => 990.00,
                'lifetime_price' => null,
                'stripe_monthly_price_id' => 'price_1SCk3s2FUElA6MJhzc4P6Hln',
                'stripe_yearly_price_id' => 'price_1SCk452FUElA6MJhQkklTplN',
                'trial_days' => 14,
                'features' => [
                    'Advanced analytics',
                    'Dedicated support',
                    'Custom templates',
                    'API access',
                    'White-label options',
                    'Custom integrations',
                    'SSO integration',
                    'Advanced security',
                ],
                'permissions' => [
                    'max_team_members' => 50,
                    'max_projects' => 100,
                    'max_storage_gb' => 100,
                    'emails_per_month' => 100000,
                    'api_calls_per_month' => 1000000,
                    'advanced_analytics' => true,
                    'api_access' => true,
                    'white_label' => true,
                    'priority_support' => true,
                    'custom_integrations' => true,
                    'sso_integration' => true,
                    'advanced_security' => true,
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 4,
            ]
        );

        // Hobby Lifetime Plan
        $hobbyLifetimePlan = Plan::firstOrCreate(
            ['slug' => 'hobby-lifetime'],
            [
                'name' => 'Hobby Lifetime',
                'slug' => 'hobby-lifetime',
                'description' => 'One-time payment for Hobby features, lifetime access',
                'type' => PlanType::LIFETIME,
                'monthly_price' => null,
                'yearly_price' => null,
                'lifetime_price' => 299.00,
                'trial_days' => 0,
                'features' => [
                    'Advanced analytics',
                    'Email support',
                    'Custom templates',
                    'API access',
                ],
                'permissions' => [
                    'max_team_members' => 3,
                    'max_projects' => 5,
                    'max_storage_gb' => 5,
                    'emails_per_month' => 5000,
                    'api_calls_per_month' => 50000,
                    'advanced_analytics' => true,
                    'api_access' => true,
                    'white_label' => false,
                    'priority_support' => false,
                    'custom_integrations' => false,
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 5,
            ]
        );

        // Growth Lifetime Plan
        $growthLifetimePlan = Plan::firstOrCreate(
            ['slug' => 'growth-lifetime'],
            [
                'name' => 'Growth Lifetime',
                'slug' => 'growth-lifetime',
                'description' => 'One-time payment for Growth features, lifetime access',
                'type' => PlanType::LIFETIME,
                'monthly_price' => null,
                'yearly_price' => null,
                'lifetime_price' => 599.00,
                'trial_days' => 0,
                'features' => [
                    'Advanced analytics',
                    'Priority support',
                    'Custom templates',
                    'API access',
                    'White-label options',
                    'Custom integrations',
                ],
                'permissions' => [
                    'max_team_members' => 10,
                    'max_projects' => 25,
                    'max_storage_gb' => 25,
                    'emails_per_month' => 25000,
                    'api_calls_per_month' => 250000,
                    'advanced_analytics' => true,
                    'api_access' => true,
                    'white_label' => true,
                    'priority_support' => true,
                    'custom_integrations' => true,
                    'sso_integration' => false,
                    'advanced_security' => false,
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 6,
            ]
        );

        // Business Lifetime Plan
        $businessLifetimePlan = Plan::firstOrCreate(
            ['slug' => 'business-lifetime'],
            [
                'name' => 'Business Lifetime',
                'slug' => 'business-lifetime',
                'description' => 'One-time payment for Business features, lifetime access',
                'type' => PlanType::LIFETIME,
                'monthly_price' => null,
                'yearly_price' => null,
                'lifetime_price' => 999.00,
                'trial_days' => 0,
                'features' => [
                    'Advanced analytics',
                    'Dedicated support',
                    'Custom templates',
                    'API access',
                    'White-label options',
                    'Custom integrations',
                    'SSO integration',
                    'Advanced security',
                ],
                'permissions' => [
                    'max_team_members' => 50,
                    'max_projects' => 100,
                    'max_storage_gb' => 100,
                    'emails_per_month' => 100000,
                    'api_calls_per_month' => 1000000,
                    'advanced_analytics' => true,
                    'api_access' => true,
                    'white_label' => true,
                    'priority_support' => true,
                    'custom_integrations' => true,
                    'sso_integration' => true,
                    'advanced_security' => true,
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 7,
            ]
        );
    }
}
