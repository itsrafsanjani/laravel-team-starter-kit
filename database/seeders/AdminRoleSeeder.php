<?php

namespace Database\Seeders;

use App\Models\AdminRole;
use Illuminate\Database\Seeder;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Management Role
        AdminRole::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full access to all admin panel features',
                'permissions' => [
                    'access_admin_panel',
                    'view_analytics',
                    'view_reports',
                    'manage_users',
                    'manage_teams',
                    'manage_plans',
                    'view_financials',
                    'manage_settings',
                    'export_data',
                ],
                'is_active' => true,
            ]
        );

        // Developers Role
        AdminRole::firstOrCreate(
            ['slug' => 'developers'],
            [
                'name' => 'Developers',
                'slug' => 'developers',
                'description' => 'Technical access for development and debugging',
                'permissions' => [
                    'access_admin_panel',
                    'view_analytics',
                    'view_reports',
                    'manage_teams',
                    'view_system_logs',
                    'debug_mode',
                    'api_access',
                ],
                'is_active' => true,
            ]
        );

        // Support Role
        AdminRole::firstOrCreate(
            ['slug' => 'support'],
            [
                'name' => 'Support',
                'slug' => 'support',
                'description' => 'Customer support and user management',
                'permissions' => [
                    'access_admin_panel',
                    'view_analytics',
                    'manage_users',
                    'manage_teams',
                    'view_support_tickets',
                    'manage_support_tickets',
                    'view_user_activity',
                ],
                'is_active' => true,
            ]
        );

        // Marketing Role
        AdminRole::firstOrCreate(
            ['slug' => 'marketing'],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'description' => 'Marketing analytics and campaign management',
                'permissions' => [
                    'access_admin_panel',
                    'view_analytics',
                    'view_reports',
                    'view_marketing_metrics',
                    'manage_campaigns',
                    'export_data',
                    'view_user_behavior',
                ],
                'is_active' => true,
            ]
        );

        // Analytics Only Role
        AdminRole::firstOrCreate(
            ['slug' => 'analytics_viewer'],
            [
                'name' => 'Analytics Viewer',
                'slug' => 'analytics_viewer',
                'description' => 'Read-only access to analytics and reports',
                'permissions' => [
                    'access_admin_panel',
                    'view_analytics',
                    'view_reports',
                    'export_data',
                ],
                'is_active' => true,
            ]
        );
    }
}
