<?php

namespace App\Console\Commands;

use App\Services\RolePermissionService;
use Illuminate\Console\Command;

class RolePermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:list {--role= : Show permissions for a specific role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all roles and permissions';

    /**
     * Execute the console command.
     */
    public function handle(RolePermissionService $rolePermissionService)
    {
        $role = $this->option('role');

        if ($role) {
            $this->showRolePermissions($rolePermissionService, $role);
        } else {
            $this->showAllRoles($rolePermissionService);
        }
    }

    private function showAllRoles(RolePermissionService $rolePermissionService)
    {
        $this->info('Available Roles:');
        $this->newLine();

        $roles = $rolePermissionService->getAllRoles();

        foreach ($roles as $roleKey => $roleInfo) {
            $this->line("<comment>{$roleKey}</comment> - {$roleInfo['name']}");
            $this->line("  Description: {$roleInfo['description']}");
            $this->line('  Permissions: '.implode(', ', $roleInfo['permissions']));
            $this->newLine();
        }

        $this->info('All Permissions:');
        $this->newLine();

        $permissions = $rolePermissionService->getAllPermissions();
        foreach ($permissions as $permission => $description) {
            $this->line("<comment>{$permission}</comment> - {$description}");
        }
    }

    private function showRolePermissions(RolePermissionService $rolePermissionService, string $role)
    {
        $roleInfo = $rolePermissionService->getRoleInfo($role);

        if (! $roleInfo) {
            $this->error("Role '{$role}' not found.");

            return;
        }

        $this->info("Role: {$roleInfo['name']} ({$role})");
        $this->line("Description: {$roleInfo['description']}");
        $this->newLine();

        $this->info('Permissions:');
        foreach ($roleInfo['permissions'] as $permission) {
            $permissions = $rolePermissionService->getAllPermissions();
            $description = $permissions[$permission] ?? 'No description';
            $this->line("  <comment>{$permission}</comment> - {$description}");
        }
    }
}
