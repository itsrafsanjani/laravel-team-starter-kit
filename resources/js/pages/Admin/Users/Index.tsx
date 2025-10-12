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
import AdminLayout from '@/layouts/admin-layout';
import { Link, router } from '@inertiajs/react';
import { Edit, Eye, Search, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface User {
  id: string;
  name: string;
  email: string;
  created_at: string;
  avatar?: string;
  is_banned: boolean;
  banned_reason?: string;
  admin_role?: Array<{
    id: string;
    name: string;
    slug: string;
  }>;
  teams: Array<{
    id: string;
    name: string;
    slug: string;
  }>;
  owned_teams: Array<{
    id: string;
    name: string;
    slug: string;
  }>;
}

interface AdminRole {
  id: string;
  name: string;
  slug: string;
}

interface UsersIndexProps {
  users: {
    data: User[];
    links: Array<{
      url: string | null;
      label: string;
      active: boolean;
    }>;
  };
  adminRoles: AdminRole[];
  filters: {
    search?: string;
    role?: string;
  };
}

export default function UsersIndex({
  users,
  adminRoles,
  filters,
}: UsersIndexProps) {
  const [search, setSearch] = useState(filters.search || '');
  const [role, setRole] = useState(filters.role || 'all');

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Users', href: '/admin/users' },
  ];

  const handleSearch = () => {
    router.get(
      '/admin/users',
      { search, role },
      {
        preserveState: true,
        replace: true,
      },
    );
  };

  const handleRoleFilter = (value: string) => {
    setRole(value);
    const roleParam = value === 'all' ? '' : value;
    router.get(
      '/admin/users',
      { search, role: roleParam },
      {
        preserveState: true,
        replace: true,
      },
    );
  };

  const handleDeleteUser = (userId: string, userName: string) => {
    if (
      confirm(
        `Are you sure you want to delete user "${userName}"? This action cannot be undone.`,
      )
    ) {
      router.delete(`/admin/users/${userId}`, {
        onSuccess: () => {
          // The page will automatically refresh with the updated user list
        },
        onError: (errors) => {
          console.error('Error deleting user:', errors);
          alert('Failed to delete user. Please try again.');
        },
      });
    }
  };

  return (
    <AdminLayout breadcrumbs={breadcrumbs}>
      <div className="space-y-6 p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Users</h1>
            <p className="text-muted-foreground">
              Manage all users and their admin roles
            </p>
          </div>
        </div>

        {/* Filters */}
        <Card>
          <CardHeader>
            <CardTitle>Filters</CardTitle>
            <CardDescription>
              Filter users by name, email, or admin role
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex gap-4">
              <div className="flex-1">
                <div className="relative">
                  <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search users..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                    className="pl-8"
                  />
                </div>
              </div>
              <Select value={role} onValueChange={handleRoleFilter}>
                <SelectTrigger className="w-[200px]">
                  <SelectValue placeholder="Filter by role" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Roles</SelectItem>
                  {adminRoles.map((role) => (
                    <SelectItem key={role.id} value={role.slug}>
                      {role.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <Button onClick={handleSearch}>Search</Button>
            </div>
          </CardContent>
        </Card>

        {/* Users Table */}
        <Card>
          <CardHeader>
            <CardTitle>All Users</CardTitle>
            <CardDescription>{users.data.length} users found</CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>User</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Admin Roles</TableHead>
                  <TableHead>Teams</TableHead>
                  <TableHead>Joined</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {users.data.map((user) => (
                  <TableRow key={user.id}>
                    <TableCell>
                      <div className="flex items-center gap-3">
                        <img
                          src={user.avatar}
                          alt={`${user.name}'s avatar`}
                          className="h-8 w-8 rounded-full object-cover"
                        />
                        <span className="font-medium">{user.name}</span>
                      </div>
                    </TableCell>
                    <TableCell>{user.email}</TableCell>
                    <TableCell>
                      {user.is_banned ? (
                        <Badge
                          variant="destructive"
                          className="flex items-center gap-1"
                        >
                          <span className="h-2 w-2 rounded-full bg-red-200"></span>
                          Banned
                        </Badge>
                      ) : (
                        <Badge
                          variant="secondary"
                          className="flex items-center gap-1"
                        >
                          <span className="h-2 w-2 rounded-full bg-green-500"></span>
                          Active
                        </Badge>
                      )}
                    </TableCell>
                    <TableCell>
                      <div className="flex flex-wrap gap-1">
                        {user.admin_role &&
                          user.admin_role.map((role) => (
                            <Badge key={role.id} variant="secondary">
                              {role.name}
                            </Badge>
                          ))}
                        {(!user.admin_role || user.admin_role.length === 0) && (
                          <span className="text-sm text-muted-foreground">
                            No roles
                          </span>
                        )}
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="text-sm">
                        <div>Owned: {user.owned_teams.length}</div>
                        <div>Member: {user.teams.length}</div>
                      </div>
                    </TableCell>
                    <TableCell>
                      {new Date(user.created_at).toLocaleDateString()}
                    </TableCell>
                    <TableCell className="text-right">
                      <div className="flex justify-end gap-2">
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/admin/users/${user.id}`}>
                            <Eye className="h-4 w-4" />
                          </Link>
                        </Button>
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/admin/users/${user.id}/edit`}>
                            <Edit className="h-4 w-4" />
                          </Link>
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          className="text-destructive"
                          onClick={() => handleDeleteUser(user.id, user.name)}
                        >
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>

            {/* Pagination */}
            {users.links.length > 3 && (
              <div className="flex items-center justify-center space-x-2 py-4">
                {users.links.map((link, index) => (
                  <Button
                    key={index}
                    variant={link.active ? 'default' : 'outline'}
                    size="sm"
                    disabled={!link.url}
                    onClick={() => link.url && router.get(link.url)}
                  >
                    {link.label === '&laquo; Previous'
                      ? 'Previous'
                      : link.label === 'Next &raquo;'
                        ? 'Next'
                        : link.label}
                  </Button>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
