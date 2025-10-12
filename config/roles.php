<?php

return [
    'roles' => [
        'owner' => [
            'name' => 'Owner',
            'description' => 'Full access to all team features',
            'permissions' => [
                'view_team',
                'update_team',
                'delete_team',
                'manage_members',
                'view_billing',
                'manage_billing',
                'invite_members',
                'remove_members',
                'view_settings',
                'update_settings',
            ],
        ],
        'admin' => [
            'name' => 'Admin',
            'description' => 'Administrative access to most team features',
            'permissions' => [
                'view_team',
                'update_team',
                'manage_members',
                'view_billing',
                'manage_billing',
                'invite_members',
                'remove_members',
                'view_settings',
                'update_settings',
            ],
        ],
        'member' => [
            'name' => 'Member',
            'description' => 'Basic access to team features',
            'permissions' => [
                'view_team',
            ],
        ],
    ],
    'permissions' => [
        'view_team' => 'View team information',
        'update_team' => 'Update team settings',
        'delete_team' => 'Delete the team',
        'manage_members' => 'Manage team members',
        'view_billing' => 'View billing information',
        'manage_billing' => 'Manage billing settings',
        'invite_members' => 'Invite new members',
        'remove_members' => 'Remove team members',
        'view_settings' => 'View team settings',
        'update_settings' => 'Update team settings',
    ],
];
