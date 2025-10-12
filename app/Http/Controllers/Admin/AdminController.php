<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController
{
    public function index(Request $request)
    {
        $stats = [
            'total_users' => User::count(),
            'total_teams' => Team::count(),
            'total_admin_roles' => AdminRole::count(),
            'active_admin_users' => User::whereHas('adminRole', function ($query) {
                $query->where('user_admin_roles.is_active', true);
            })->count(),
        ];

        $recent_users = User::with(['adminRole'])
            ->latest()
            ->limit(5)
            ->get();

        $recent_teams = Team::with(['owner'])
            ->latest()
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recent_users' => $recent_users,
            'recent_teams' => $recent_teams,
        ]);
    }
}
