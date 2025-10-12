import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import AdminLayout from '@/layouts/admin-layout';
import { Link, router, useForm } from '@inertiajs/react';
import { Edit, Plus, Trash2, X } from 'lucide-react';
import { useState } from 'react';

interface User {
  id: string;
  name: string;
  email: string;
  created_at: string;
  admin_role: Array<{
    id: string;
    name: string;
    slug: string;
    pivot: {
      is_active: boolean;
      assigned_at: string;
      expires_at: string | null;
    };
  }>;
  teams: Array<{
    id: string;
    name: string;
    slug: string;
    pivot: {
      role: string;
      joined_at: string;
    };
  }>;
  owned_teams: Array<{
    id: string;
    name: string;
    slug: string;
    type: string;
  }>;
}

interface AdminRole {
  id: string;
  name: string;
  slug: string;
}

interface UserShowProps {
  user: User;
  availableRoles: AdminRole[];
}

export default function UserShow({ user, availableRoles }: UserShowProps) {
  const [showAssignRole, setShowAssignRole] = useState(false);

  const { data, setData, post, processing, errors } = useForm({
    role_id: user.admin_role.length > 0 ? user.admin_role[0].id.toString() : '',
    expires_at:
      user.admin_role.length > 0 && user.admin_role[0].pivot.expires_at
        ? new Date(user.admin_role[0].pivot.expires_at)
            .toISOString()
            .slice(0, 16)
        : '',
  });

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Users', href: '/admin/users' },
    { title: user.name, href: `/admin/users/${user.id}` },
  ];

  const handleAssignRole = (e: React.FormEvent) => {
    e.preventDefault();
    post(`/admin/users/${user.id}/assign-role`);
  };

  const handleRemoveRole = () => {
    router.delete(`/admin/users/${user.id}/remove-role`);
  };

  return (
    <AdminLayout breadcrumbs={breadcrumbs}>
      <div className="space-y-6 p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">{user.name}</h1>
            <p className="text-muted-foreground">{user.email}</p>
          </div>
          <div className="flex gap-2">
            <Button asChild variant="outline">
              <Link href={`/admin/users/${user.id}/edit`}>
                <Edit className="mr-2 h-4 w-4" />
                Edit User
              </Link>
            </Button>
            <Button variant="destructive">
              <Trash2 className="mr-2 h-4 w-4" />
              Delete User
            </Button>
          </div>
        </div>

        <div className="grid gap-6 md:grid-cols-2">
          {/* User Details */}
          <Card>
            <CardHeader>
              <CardTitle>User Details</CardTitle>
              <CardDescription>
                Basic information about this user
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <Label htmlFor="name">Name</Label>
                <Input id="name" value={user.name} disabled />
              </div>
              <div>
                <Label htmlFor="email">Email</Label>
                <Input id="email" value={user.email} disabled />
              </div>
              <div>
                <Label>Joined</Label>
                <p className="text-sm text-muted-foreground">
                  {new Date(user.created_at).toLocaleString()}
                </p>
              </div>
            </CardContent>
          </Card>

          {/* Admin Roles */}
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <div>
                  <CardTitle>Admin Role</CardTitle>
                  <CardDescription>
                    Role assigned to this user (one role maximum)
                  </CardDescription>
                </div>
                {user.admin_role.length === 0 ? (
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setShowAssignRole(!showAssignRole)}
                  >
                    <Plus className="mr-2 h-4 w-4" />
                    Assign Role
                  </Button>
                ) : (
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setShowAssignRole(!showAssignRole)}
                  >
                    <Edit className="mr-2 h-4 w-4" />
                    Change Role
                  </Button>
                )}
              </div>
            </CardHeader>
            <CardContent>
              {showAssignRole && (
                <form
                  onSubmit={handleAssignRole}
                  className="mb-4 space-y-4 rounded-lg border p-4"
                >
                  <div>
                    <Label htmlFor="role">
                      {user.admin_role.length > 0
                        ? 'Change Role'
                        : 'Select Role'}
                    </Label>
                    <Select
                      value={data.role_id}
                      onValueChange={(value) => setData('role_id', value)}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Choose a role" />
                      </SelectTrigger>
                      <SelectContent>
                        {availableRoles.map((role) => (
                          <SelectItem key={role.id} value={role.id.toString()}>
                            {role.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                  <div>
                    <Label htmlFor="expires_at">Expires At (Optional)</Label>
                    <Input
                      id="expires_at"
                      type="datetime-local"
                      value={data.expires_at}
                      onChange={(e) => setData('expires_at', e.target.value)}
                    />
                  </div>
                  <div className="flex gap-2">
                    <Button type="submit" disabled={processing}>
                      {user.admin_role.length > 0
                        ? 'Change Role'
                        : 'Assign Role'}
                    </Button>
                    <Button
                      type="button"
                      variant="outline"
                      onClick={() => setShowAssignRole(false)}
                    >
                      Cancel
                    </Button>
                  </div>
                </form>
              )}

              <div className="space-y-2">
                {user.admin_role.length > 0 ? (
                  <div className="flex items-center justify-between rounded-lg border p-3">
                    <div>
                      <Badge variant="secondary">
                        {user.admin_role[0].name}
                      </Badge>
                      <div className="text-sm text-muted-foreground">
                        Assigned:{' '}
                        {new Date(
                          user.admin_role[0].pivot.assigned_at,
                        ).toLocaleDateString()}
                        {user.admin_role[0].pivot.expires_at && (
                          <span>
                            {' '}
                            • Expires:{' '}
                            {new Date(
                              user.admin_role[0].pivot.expires_at,
                            ).toLocaleDateString()}
                          </span>
                        )}
                      </div>
                    </div>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => handleRemoveRole()}
                    >
                      <X className="h-4 w-4" />
                    </Button>
                  </div>
                ) : (
                  <p className="py-4 text-center text-muted-foreground">
                    No admin role assigned
                  </p>
                )}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Teams */}
        <Card>
          <CardHeader>
            <CardTitle>Teams</CardTitle>
            <CardDescription>
              Teams this user owns or is a member of
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {/* Owned Teams */}
              {user.owned_teams.length > 0 && (
                <div>
                  <h4 className="mb-2 font-medium">Owned Teams</h4>
                  <div className="space-y-2">
                    {user.owned_teams.map((team) => (
                      <div
                        key={team.id}
                        className="flex items-center justify-between rounded border p-2"
                      >
                        <div>
                          <span className="font-medium">{team.name}</span>
                          <Badge variant="default" className="ml-2">
                            Owner
                          </Badge>
                          <Badge variant="outline" className="ml-1">
                            {team.type}
                          </Badge>
                        </div>
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/admin/teams/${team.slug}`}>View</Link>
                        </Button>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Member Teams */}
              {user.teams.length > 0 && (
                <div>
                  <h4 className="mb-2 font-medium">Member Teams</h4>
                  <div className="space-y-2">
                    {user.teams.map((team) => (
                      <div
                        key={team.id}
                        className="flex items-center justify-between rounded border p-2"
                      >
                        <div>
                          <span className="font-medium">{team.name}</span>
                          <Badge variant="secondary" className="ml-2">
                            {team.pivot.role}
                          </Badge>
                        </div>
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/admin/teams/${team.slug}`}>View</Link>
                        </Button>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {user.owned_teams.length === 0 && user.teams.length === 0 && (
                <p className="py-4 text-center text-muted-foreground">
                  User is not part of any teams
                </p>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
