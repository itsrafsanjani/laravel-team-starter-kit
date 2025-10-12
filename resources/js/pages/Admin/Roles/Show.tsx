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
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import AdminLayout from '@/layouts/admin-layout';
import { Link } from '@inertiajs/react';
import { ArrowLeft, Calendar, Edit, Shield, Trash2, Users } from 'lucide-react';

interface AdminRole {
  id: number;
  name: string;
  slug: string;
  description: string;
  permissions: string[];
  is_active: boolean;
  created_at: string;
  users: Array<{
    id: number;
    name: string;
    email: string;
    pivot: {
      is_active: boolean;
      assigned_at: string;
      expires_at: string | null;
    };
  }>;
}

interface RoleShowProps {
  role: AdminRole;
}

export default function RoleShow({ role }: RoleShowProps) {
  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Admin Roles', href: '/admin/roles' },
    { title: role.name, href: `/admin/roles/${role.id}` },
  ];

  return (
    <AdminLayout breadcrumbs={breadcrumbs}>
      <div className="space-y-6 p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button asChild variant="outline" size="sm">
              <Link href="/admin/roles">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to Roles
              </Link>
            </Button>
            <div>
              <h1 className="text-3xl font-bold tracking-tight">{role.name}</h1>
              <p className="text-muted-foreground">/{role.slug}</p>
            </div>
          </div>
          <div className="flex gap-2">
            <Button asChild variant="outline">
              <Link href={`/admin/roles/${role.id}/edit`}>
                <Edit className="mr-2 h-4 w-4" />
                Edit Role
              </Link>
            </Button>
            <Button variant="destructive">
              <Trash2 className="mr-2 h-4 w-4" />
              Delete Role
            </Button>
          </div>
        </div>

        <div className="grid gap-6 md:grid-cols-2">
          {/* Role Details */}
          <Card>
            <CardHeader>
              <CardTitle>Role Details</CardTitle>
              <CardDescription>
                Basic information about this admin role
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center gap-2">
                <Shield className="h-4 w-4 text-muted-foreground" />
                <span className="font-medium">{role.name}</span>
                <Badge variant={role.is_active ? 'default' : 'secondary'}>
                  {role.is_active ? 'Active' : 'Inactive'}
                </Badge>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">
                  Slug: /{role.slug}
                </p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">
                  {role.description || 'No description provided'}
                </p>
              </div>
              <div className="flex items-center gap-2">
                <Calendar className="h-4 w-4 text-muted-foreground" />
                <span className="text-sm text-muted-foreground">
                  Created: {new Date(role.created_at).toLocaleDateString()}
                </span>
              </div>
            </CardContent>
          </Card>

          {/* Permissions */}
          <Card>
            <CardHeader>
              <CardTitle>Permissions</CardTitle>
              <CardDescription>
                Permissions assigned to this role
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                {role.permissions.map((permission) => (
                  <div
                    key={permission}
                    className="flex items-center justify-between rounded border p-2"
                  >
                    <span className="text-sm font-medium">
                      {permission
                        .replace(/_/g, ' ')
                        .replace(/\b\w/g, (l) => l.toUpperCase())}
                    </span>
                    <Badge variant="outline">Granted</Badge>
                  </div>
                ))}
                {role.permissions.length === 0 && (
                  <p className="py-4 text-center text-muted-foreground">
                    No permissions assigned
                  </p>
                )}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Users with this Role */}
        <Card>
          <CardHeader>
            <CardTitle>Users with this Role</CardTitle>
            <CardDescription>
              All users who have been assigned this admin role (
              {role.users.length} total)
            </CardDescription>
          </CardHeader>
          <CardContent>
            {role.users.length > 0 ? (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Assigned</TableHead>
                    <TableHead>Expires</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {role.users.map((user) => (
                    <TableRow key={user.id}>
                      <TableCell className="font-medium">{user.name}</TableCell>
                      <TableCell>{user.email}</TableCell>
                      <TableCell>
                        <Badge
                          variant={
                            user.pivot.is_active ? 'default' : 'secondary'
                          }
                        >
                          {user.pivot.is_active ? 'Active' : 'Inactive'}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        {new Date(user.pivot.assigned_at).toLocaleDateString()}
                      </TableCell>
                      <TableCell>
                        {user.pivot.expires_at
                          ? new Date(user.pivot.expires_at).toLocaleDateString()
                          : 'Never'}
                      </TableCell>
                      <TableCell className="text-right">
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/admin/users/${user.id}`}>
                            View User
                          </Link>
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            ) : (
              <div className="py-8 text-center">
                <Users className="mx-auto h-12 w-12 text-muted-foreground" />
                <h3 className="mt-2 text-sm font-semibold text-gray-900">
                  No users assigned
                </h3>
                <p className="mt-1 text-sm text-muted-foreground">
                  This role hasn't been assigned to any users yet.
                </p>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
