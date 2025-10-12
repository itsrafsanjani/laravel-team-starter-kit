<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminUserController
{
    public function index(Request $request)
    {
        // Check if user has manage_users permission
        if (! $request->user()->hasPermission('manage_users')) {
            abort(403, 'Access denied. You do not have permission to manage users.');
        }

        $query = User::with(['adminRole', 'teams', 'ownedTeams']);

        if ($request->filled('search') && ! empty(trim($request->search))) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $query->whereHas('adminRole', function ($q) use ($request) {
                $q->where('slug', $request->role);
            });
        }

        $users = $query->paginate(15)
            ->withQueryString();

        $adminRoles = AdminRole::active()->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'adminRoles' => $adminRoles,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function show(User $user)
    {
        $user->load(['adminRole', 'teams', 'ownedTeams']);

        $availableRoles = AdminRole::active()->get();

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'availableRoles' => $availableRoles,
        ]);
    }

    public function edit(User $user)
    {
        $user->load(['adminRole', 'teams', 'ownedTeams']);

        $availableRoles = AdminRole::active()->get();

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'availableRoles' => $availableRoles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|string|max:255|url',
            'is_banned' => 'boolean',
            'banned_reason' => 'nullable|string|max:1000',
        ]);

        $updateData = $request->only(['name', 'email', 'avatar', 'is_banned', 'banned_reason']);

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        // Clear banned_reason if user is not banned
        if (! $request->boolean('is_banned')) {
            $updateData['banned_reason'] = null;
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting users with admin roles
        if ($user->adminRole()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete user with admin role. Remove admin role first.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:admin_roles,id',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $role = AdminRole::findOrFail($request->role_id);

        // Remove any existing admin roles first
        $user->adminRole()->detach();

        // Assign the new role
        $user->adminRole()->attach($role->id, [
            'is_active' => true,
            'assigned_at' => now(),
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->back()->with('success', 'Admin role assigned successfully.');
    }

    public function removeRole(User $user)
    {
        if (! $user->adminRole()->exists()) {
            return redirect()->back()->with('error', 'User has no admin role to remove.');
        }

        $user->adminRole()->detach();

        return redirect()->back()->with('success', 'Admin role removed successfully.');
    }
}
