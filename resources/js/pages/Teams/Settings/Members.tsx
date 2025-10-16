import InviteMemberModal from '@/components/teams/invite-member-modal';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { useDebounce } from '@/hooks/use-debounce';
import AppLayout from '@/layouts/app-layout';
import TeamSettingsLayout from '@/layouts/teams/layout';
import teamRoutes from '@/routes/team';
import teams from '@/routes/teams';
import { type BreadcrumbItem, type Team } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import {
  Mail,
  MoreHorizontal,
  Search,
  Trash2,
  User as UserIcon,
  UserPlus,
} from 'lucide-react';
import { useEffect, useState } from 'react';

interface Role {
  key: string;
  name: string;
}

interface PaginationData {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

interface Props {
  team: Team;
  members: PaginationData;
  userRole: string;
  availableRoles: Role[];
  filters: {
    search: string;
    role: string;
  };
}

export default function TeamMembersSettings({
  team,
  members,
  userRole,
  availableRoles,
  filters,
}: Props) {
  const [isInviteModalOpen, setIsInviteModalOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState(filters.search);
  const [roleFilter, setRoleFilter] = useState(filters.role || 'all');

  // Debounce the search term to avoid excessive API calls
  const debouncedSearchTerm = useDebounce(searchTerm, 300);

  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Teams',
      href: teamRoutes.dashboard({ team: team.slug }).url,
    },
    {
      title: 'Team Settings',
      href: '',
    },
  ];

  // Handle debounced search
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    if (debouncedSearchTerm) {
      params.set('search', debouncedSearchTerm);
    } else {
      params.delete('search');
    }
    router.get(
      window.location.pathname + '?' + params.toString(),
      {},
      {
        preserveState: true,
        replace: true,
      },
    );
  }, [debouncedSearchTerm]);

  const [roleChangeDialog, setRoleChangeDialog] = useState<{
    isOpen: boolean;
    userId: number | null;
    userName: string;
    newRole: string;
    currentRole: string;
  }>({
    isOpen: false,
    userId: null,
    userName: '',
    newRole: '',
    currentRole: '',
  });

  const [memberDeleteDialog, setMemberDeleteDialog] = useState<{
    isOpen: boolean;
    userId: number | null;
    userName: string;
  }>({
    isOpen: false,
    userId: null,
    userName: '',
  });

  const [invitationDeleteDialog, setInvitationDeleteDialog] = useState<{
    isOpen: boolean;
    invitationId: string | null;
    email: string;
  }>({
    isOpen: false,
    invitationId: null,
    email: '',
  });

  // Separate forms for DELETE requests (no data needed)
  const { delete: deleteInvitation } = useForm();
  const { delete: deleteMember } = useForm();

  // Handle search - only update local state, debounced search is handled by useEffect
  const handleSearch = (value: string) => {
    setSearchTerm(value);
  };

  // Handle role filter
  const handleRoleFilter = (value: string) => {
    setRoleFilter(value);
    const params = new URLSearchParams(window.location.search);
    if (value && value !== 'all') {
      params.set('role', value);
    } else {
      params.delete('role');
    }
    router.get(
      window.location.pathname + '?' + params.toString(),
      {},
      {
        preserveState: true,
        replace: true,
      },
    );
  };

  const getRoleBadgeVariant = (role: string) => {
    switch (role) {
      case 'owner':
        return 'default';
      case 'admin':
        return 'secondary';
      default:
        return 'outline';
    }
  };

  const handleRoleChange = (userId: number, newRole: string) => {
    const user = team.users.find((u) => u.id === userId);
    if (!user) return;

    setRoleChangeDialog({
      isOpen: true,
      userId,
      userName: user.name,
      newRole,
      currentRole: user.pivot?.role || '',
    });
  };

  const confirmRoleChange = () => {
    if (roleChangeDialog.userId && roleChangeDialog.newRole) {
      router.put(
        teams.members.updateRole({
          team: team.slug,
          user: roleChangeDialog.userId,
        }).url,
        {
          role: roleChangeDialog.newRole,
        },
      );
    }
    setRoleChangeDialog({
      isOpen: false,
      userId: null,
      userName: '',
      newRole: '',
      currentRole: '',
    });
  };

  const cancelRoleChange = () => {
    setRoleChangeDialog({
      isOpen: false,
      userId: null,
      userName: '',
      newRole: '',
      currentRole: '',
    });
  };

  const handleRemoveMember = (userId: number) => {
    const user = team.users.find((u) => u.id === userId);
    if (!user) return;

    setMemberDeleteDialog({
      isOpen: true,
      userId,
      userName: user.name,
    });
  };

  const confirmRemoveMember = () => {
    if (memberDeleteDialog.userId) {
      deleteMember(
        teams.settings.members.destroy({
          team: team.slug,
          user: memberDeleteDialog.userId,
        }).url,
      );
    }
    setMemberDeleteDialog({
      isOpen: false,
      userId: null,
      userName: '',
    });
  };

  const cancelRemoveMember = () => {
    setMemberDeleteDialog({
      isOpen: false,
      userId: null,
      userName: '',
    });
  };

  const handleRemoveInvitation = (invitationId: string) => {
    const invitation = team.invitations.find((i) => i.id === invitationId);
    if (!invitation) return;

    setInvitationDeleteDialog({
      isOpen: true,
      invitationId,
      email: invitation.email,
    });
  };

  const confirmRemoveInvitation = () => {
    if (invitationDeleteDialog.invitationId) {
      deleteInvitation(
        teams.invitations.remove({
          team: team.slug,
          invitation: invitationDeleteDialog.invitationId,
        }).url,
      );
    }
    setInvitationDeleteDialog({
      isOpen: false,
      invitationId: null,
      email: '',
    });
  };

  const cancelRemoveInvitation = () => {
    setInvitationDeleteDialog({
      isOpen: false,
      invitationId: null,
      email: '',
    });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${team.name} - Members`} />

      <TeamSettingsLayout team={team}>
        <div className="space-y-6">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-lg font-medium">Team Members</h3>
              <p className="text-sm text-gray-600 dark:text-gray-400">
                Manage your team members and their roles
              </p>
            </div>
            <Button onClick={() => setIsInviteModalOpen(true)}>
              <UserPlus className="mr-2 h-4 w-4" />
              Invite Member
            </Button>
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Current Members</CardTitle>
              <CardDescription>
                {members.total} member
                {members.total !== 1 ? 's' : ''} in your team
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {/* Search and Filter Controls */}
                <div className="flex gap-4">
                  <div className="relative flex-1">
                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <Input
                      placeholder="Search members..."
                      value={searchTerm}
                      onChange={(e) => handleSearch(e.target.value)}
                      className="pl-10"
                    />
                  </div>
                  <Select value={roleFilter} onValueChange={handleRoleFilter}>
                    <SelectTrigger className="w-[180px]">
                      <SelectValue placeholder="Filter by role" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All Roles</SelectItem>
                      {availableRoles.map((role) => (
                        <SelectItem key={role.key} value={role.key}>
                          {role.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                {/* Members Table */}
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Member</TableHead>
                      <TableHead>Role</TableHead>
                      <TableHead className="w-[100px]">Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {team.users.map((user) => (
                      <TableRow key={user.id}>
                        <TableCell>
                          <div className="flex items-center space-x-3">
                            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700">
                              <UserIcon className="h-4 w-4" />
                            </div>
                            <div>
                              <div className="font-medium">{user.name}</div>
                              <div className="text-sm text-gray-600 dark:text-gray-400">
                                {user.email}
                              </div>
                            </div>
                          </div>
                        </TableCell>
                        <TableCell>
                          {userRole === 'owner' &&
                          user.pivot?.role !== 'owner' ? (
                            <Select
                              value={user.pivot?.role || ''}
                              onValueChange={(newRole) =>
                                handleRoleChange(user.id, newRole)
                              }
                            >
                              <SelectTrigger className="w-[140px]">
                                <SelectValue />
                              </SelectTrigger>
                              <SelectContent>
                                {availableRoles.map((role) => (
                                  <SelectItem key={role.key} value={role.key}>
                                    {role.name}
                                  </SelectItem>
                                ))}
                              </SelectContent>
                            </Select>
                          ) : (
                            <Badge
                              variant={getRoleBadgeVariant(
                                user.pivot?.role || '',
                              )}
                            >
                              {user.pivot?.role || ''}
                            </Badge>
                          )}
                        </TableCell>
                        <TableCell className="text-right">
                          {userRole === 'owner' &&
                            user.pivot?.role !== 'owner' && (
                              <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                  <Button variant="ghost" size="sm">
                                    <MoreHorizontal className="h-4 w-4" />
                                  </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                  <DropdownMenuItem
                                    className="text-red-600"
                                    onClick={() => handleRemoveMember(user.id)}
                                  >
                                    <Trash2 className="mr-2 h-4 w-4" />
                                    Remove Member
                                  </DropdownMenuItem>
                                </DropdownMenuContent>
                              </DropdownMenu>
                            )}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>

                {team.users.length === 0 && (searchTerm || roleFilter) && (
                  <div className="py-8 text-center text-gray-500">
                    No members found matching your criteria
                  </div>
                )}

                {/* Pagination Controls */}
                {members.last_page > 1 && (
                  <div className="flex items-center justify-between">
                    <div className="text-sm text-gray-500">
                      Showing {members.from} to {members.to} of {members.total}{' '}
                      members
                    </div>
                    <div className="flex items-center space-x-2">
                      <Button
                        variant="outline"
                        size="sm"
                        disabled={members.current_page === 1}
                        onClick={() => {
                          const params = new URLSearchParams(
                            window.location.search,
                          );
                          params.set(
                            'page',
                            (members.current_page - 1).toString(),
                          );
                          router.get(
                            window.location.pathname + '?' + params.toString(),
                            {},
                            {
                              preserveState: true,
                              replace: true,
                            },
                          );
                        }}
                      >
                        Previous
                      </Button>
                      <span className="text-sm">
                        Page {members.current_page} of {members.last_page}
                      </span>
                      <Button
                        variant="outline"
                        size="sm"
                        disabled={members.current_page === members.last_page}
                        onClick={() => {
                          const params = new URLSearchParams(
                            window.location.search,
                          );
                          params.set(
                            'page',
                            (members.current_page + 1).toString(),
                          );
                          router.get(
                            window.location.pathname + '?' + params.toString(),
                            {},
                            {
                              preserveState: true,
                              replace: true,
                            },
                          );
                        }}
                      >
                        Next
                      </Button>
                    </div>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>

          {team.invitations.length > 0 && (
            <Card>
              <CardHeader>
                <CardTitle>Pending Invitations</CardTitle>
                <CardDescription>
                  {team.invitations.length} pending invitation
                  {team.invitations.length !== 1 ? 's' : ''}
                </CardDescription>
              </CardHeader>
              <CardContent>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Email</TableHead>
                      <TableHead>Role</TableHead>
                      <TableHead>Invited</TableHead>
                      <TableHead>Expires</TableHead>
                      <TableHead className="w-[100px]">Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {team.invitations.map((invitation) => (
                      <TableRow key={invitation.id}>
                        <TableCell>
                          <div className="flex items-center space-x-3">
                            <Mail className="h-4 w-4 text-gray-400" />
                            <span className="font-medium">
                              {invitation.email}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline">{invitation.role}</Badge>
                        </TableCell>
                        <TableCell>
                          {new Date(invitation.created_at).toLocaleDateString()}
                        </TableCell>
                        <TableCell>
                          {new Date(invitation.expires_at).toLocaleDateString()}
                        </TableCell>
                        <TableCell>
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() =>
                              handleRemoveInvitation(invitation.id)
                            }
                          >
                            <Trash2 className="h-4 w-4" />
                          </Button>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </CardContent>
            </Card>
          )}
        </div>

        <InviteMemberModal
          isOpen={isInviteModalOpen}
          onClose={() => setIsInviteModalOpen(false)}
          inviteUrl={teams.members.invite({ team: team.slug }).url}
          availableRoles={availableRoles}
        />

        {/* Role Change Confirmation Dialog */}
        <AlertDialog
          open={roleChangeDialog.isOpen}
          onOpenChange={cancelRoleChange}
        >
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Change Member Role</AlertDialogTitle>
              <AlertDialogDescription>
                Are you sure you want to change{' '}
                <strong>{roleChangeDialog.userName}</strong>'s role from{' '}
                <strong>{roleChangeDialog.currentRole}</strong> to{' '}
                <strong>{roleChangeDialog.newRole}</strong>?
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel onClick={cancelRoleChange}>
                Cancel
              </AlertDialogCancel>
              <AlertDialogAction onClick={confirmRoleChange}>
                Change Role
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>

        {/* Member Removal Confirmation Dialog */}
        <AlertDialog
          open={memberDeleteDialog.isOpen}
          onOpenChange={cancelRemoveMember}
        >
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Remove Team Member</AlertDialogTitle>
              <AlertDialogDescription>
                Are you sure you want to remove{' '}
                <strong>{memberDeleteDialog.userName}</strong> from the team?
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel onClick={cancelRemoveMember}>
                Cancel
              </AlertDialogCancel>
              <AlertDialogAction
                onClick={confirmRemoveMember}
                className="bg-red-600 hover:bg-red-700"
              >
                Remove Member
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>

        {/* Invitation Cancellation Confirmation Dialog */}
        <AlertDialog
          open={invitationDeleteDialog.isOpen}
          onOpenChange={cancelRemoveInvitation}
        >
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Cancel Invitation</AlertDialogTitle>
              <AlertDialogDescription>
                Are you sure you want to cancel the invitation for{' '}
                <strong>{invitationDeleteDialog.email}</strong>?
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel onClick={cancelRemoveInvitation}>
                Cancel
              </AlertDialogCancel>
              <AlertDialogAction
                onClick={confirmRemoveInvitation}
                className="bg-red-600 hover:bg-red-700"
              >
                Cancel Invitation
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </TeamSettingsLayout>
    </AppLayout>
  );
}
