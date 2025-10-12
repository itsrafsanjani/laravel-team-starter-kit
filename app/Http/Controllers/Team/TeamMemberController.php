<?php

namespace App\Http\Controllers\Team;

use App\Actions\Teams\InviteTeamMember;
use App\Actions\Teams\RemoveTeamMember;
use App\Actions\Teams\UpdateTeamMemberRole;
use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamMemberController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $team = team();
        $this->authorize('manageMembers', $team);

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

    public function invite(Request $request, InviteTeamMember $inviteTeamMember)
    {
        $team = team();
        $this->authorize('inviteMembers', $team);

        $availableRoles = array_keys(config('roles.roles'));

        $request->validate([
            'email' => 'required|email|max:255',
            'role' => 'required|in:'.implode(',', $availableRoles),
        ]);

        try {
            $inviteTeamMember->execute($team, $request->user(), $request->email, $request->role);

            return redirect()->back()
                ->with('success', 'Invitation sent successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function remove(Request $request, $team, User $user, RemoveTeamMember $removeTeamMember)
    {
        $team = team();
        $this->authorize('removeMembers', $team);

        try {
            $removeTeamMember->execute($team, $request->user(), $user);

            return redirect()->back()
                ->with('success', 'Team member removed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateRole(Request $request, $team, $user, UpdateTeamMemberRole $updateTeamMemberRole)
    {
        $team = team();
        $this->authorize('manageMembers', $team);

        // Resolve the user if it's an ID
        if (is_numeric($user)) {
            $user = User::findOrFail($user);
        }

        $availableRoles = array_keys(config('roles.roles'));

        $request->validate([
            'role' => 'required|in:'.implode(',', $availableRoles),
        ]);

        try {
            $updateTeamMemberRole->execute($team, $request->user(), $user, $request->role);

            return redirect()->back()
                ->with('success', 'Member role updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function removeInvitation(Request $request, $team, TeamInvitation $invitation)
    {
        $team = team();
        $this->authorize('manageMembers', $team);

        if (! $invitation) {
            return redirect()->back()
                ->withErrors(['error' => 'Invitation not found.']);
        }

        // Ensure the invitation belongs to this team
        if ($invitation->team_id !== $team->id) {
            return redirect()->back()
                ->withErrors(['error' => 'Invitation not found.']);
        }

        try {
            $invitation->delete();

            return redirect()->back()
                ->with('success', 'Invitation cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
