<?php

namespace Database\Seeders;

use App\Actions\Teams\CreateTeam;
use App\Models\AdminRole;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function __construct(
        private CreateTeam $createTeam
    ) {}

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Seed plans and admin roles first
        $this->call([
            PlanSeeder::class,
            AdminRoleSeeder::class,
        ]);

        // Create a personal team for the admin user
        $team = $this->createTeam->execute($user, [
            'name' => $user->name,
            'type' => 'personal',
        ]);

        // Assign the user the Management admin role for full access
        $managementRole = AdminRole::where('slug', 'admin')->first();
        if ($managementRole) {
            $user->adminRole()->attach($managementRole->id, [
                'is_active' => true,
                'assigned_at' => now(),
            ]);
        }
    }
}
