<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminRoleController
{
    public function index(Request $request)
    {
        $query = AdminRole::with(['users']);

        if ($request->filled('search') && ! empty(trim($request->search))) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->active !== null && $request->active !== 'all') {
            $query->where('is_active', $request->active);
        }

        $roles = $query->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search', 'active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Roles/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:admin_roles',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
            'is_active' => 'boolean',
        ]);

        AdminRole::create($request->all());

        return redirect()->route('admin.roles.index')->with('success', 'Admin role created successfully.');
    }

    public function show(AdminRole $role)
    {
        $role->load(['users']);

        return Inertia::render('Admin/Roles/Show', [
            'role' => $role,
        ]);
    }

    public function edit(AdminRole $role)
    {
        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
        ]);
    }

    public function update(Request $request, AdminRole $role)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('admin_roles')->ignore($role->id)],
            'description' => 'nullable|string|max:1000',
            'permissions' => 'array',
            'permissions.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $role->update($request->all());

        return redirect()->back()->with('success', 'Admin role updated successfully.');
    }

    public function destroy(AdminRole $role)
    {
        // Prevent deleting roles that are assigned to users
        if ($role->users()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete role that is assigned to users.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Admin role deleted successfully.');
    }
}
