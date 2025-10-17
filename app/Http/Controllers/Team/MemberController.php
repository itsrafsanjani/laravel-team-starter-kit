<?php

namespace App\Http\Controllers\Team;

use App\Actions\Teams\RemoveTeamMember;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $team = team();
        Gate::authorize('manageMembers', $team);

        $roles = config('roles.roles', []);
        $availableRoles = [];
        foreach ($roles as $key => $role) {
            $availableRoles[] = [
                'key' => $key,
                'name' => $role['name'] ?? $key,
            ];
        }

        // Get search and filter parameters
        $search = $request->get('search', '');
        $roleFilter = $request->get('role', '');
        $perPage = $request->get('per_page', 15);

        // Build query for team members
        $membersQuery = $team->users()->withPivot(['role', 'joined_at']);

        // Apply search filter
        if (! empty($search)) {
            $membersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if (! empty($roleFilter)) {
            $membersQuery->wherePivot('role', $roleFilter);
        }

        // Get paginated members
        $members = $membersQuery->paginate($perPage);

        // Get invitations (not paginated for now, but could be added later)
        $invitations = $team->invitations;

        return Inertia::render('Teams/Settings/Members', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'type' => $team->type,
                'users' => $members->items(),
                'invitations' => $invitations,
            ],
            'members' => $members,
            'userRole' => $request->user()->teamRole($team),
            'availableRoles' => $availableRoles,
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
            ],
        ]);
    }

    public function destroy(Request $request, $team, User $user, RemoveTeamMember $removeTeamMember)
    {
        $team = team();
        Gate::authorize('removeMembers', $team);

        try {
            $removeTeamMember->execute($team, $request->user(), $user);

            return redirect()->back()
                ->with('success', 'Team member removed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
