<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Teams\DeleteTeam;
use App\Actions\Teams\UpdateTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminTeamController
{
    public function index(Request $request)
    {
        $query = Team::with(['owner', 'users']);

        if ($request->filled('search') && ! empty(trim($request->search))) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $teams = $query->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Teams/Index', [
            'teams' => $teams,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function show(Team $team)
    {
        $team->load(['owner', 'users', 'subscriptions']);

        return Inertia::render('Admin/Teams/Show', [
            'team' => $team,
        ]);
    }

    public function edit(Team $team)
    {
        $team->load(['owner', 'users']);
        $users = User::select('id', 'name', 'email')->get();

        return Inertia::render('Admin/Teams/Edit', [
            'team' => $team,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Team $team, UpdateTeam $updateTeam)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('teams')->ignore($team->id)],
            'type' => 'required|in:personal,business',
            'user_id' => 'required|exists:users,id',
            'website' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $updateTeam->execute($team, $user, $request->all());

            return redirect()->back()->with('success', 'Team updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Team $team, DeleteTeam $deleteTeam)
    {
        try {
            // For admin deletion, we'll use a system user or the first admin user
            $adminUser = User::whereHas('adminRoles')->first() ?? User::first();

            if (! $adminUser) {
                throw new \Exception('No admin user found to perform team deletion.');
            }

            $deleteTeam->execute($team, $adminUser);

            return redirect()->route('admin.teams.index')->with('success', 'Team deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
