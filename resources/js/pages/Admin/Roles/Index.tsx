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
import { Edit, Eye, Plus, Search, Trash2 } from 'lucide-react';
import { useState } from 'react';

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
  }>;
}

interface RolesIndexProps {
  roles: {
    data: AdminRole[];
    links: Array<{
      url: string | null;
      label: string;
      active: boolean;
    }>;
  };
  filters: {
    search?: string;
    active?: string;
  };
}

export default function RolesIndex({ roles, filters }: RolesIndexProps) {
  const [search, setSearch] = useState(filters.search || '');
  const [active, setActive] = useState(filters.active || 'all');

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Admin Roles', href: '/admin/roles' },
  ];

  const handleSearch = () => {
    router.get(
      '/admin/roles',
      { search, active },
      {
        preserveState: true,
        replace: true,
      },
    );
  };

  const handleActiveFilter = (value: string) => {
    setActive(value);
    const activeParam = value === 'all' ? '' : value;
    router.get(
      '/admin/roles',
      { search, active: activeParam },
      {
        preserveState: true,
        replace: true,
      },
    );
  };

  return (
    <AdminLayout breadcrumbs={breadcrumbs}>
      <div className="space-y-6 p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Admin Roles</h1>
            <p className="text-muted-foreground">
              Manage admin roles and their permissions
            </p>
          </div>
          <Button asChild>
            <Link href="/admin/roles/create">
              <Plus className="mr-2 h-4 w-4" />
              Create Role
            </Link>
          </Button>
        </div>

        {/* Filters */}
        <Card>
          <CardHeader>
            <CardTitle>Filters</CardTitle>
            <CardDescription>
              Filter roles by name, description, or status
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex gap-4">
              <div className="flex-1">
                <div className="relative">
                  <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search roles..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                    className="pl-8"
                  />
                </div>
              </div>
              <Select value={active} onValueChange={handleActiveFilter}>
                <SelectTrigger className="w-[200px]">
                  <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="1">Active</SelectItem>
                  <SelectItem value="0">Inactive</SelectItem>
                </SelectContent>
              </Select>
              <Button onClick={handleSearch}>Search</Button>
            </div>
          </CardContent>
        </Card>

        {/* Roles Table */}
        <Card>
          <CardHeader>
            <CardTitle>All Admin Roles</CardTitle>
            <CardDescription>{roles.data.length} roles found</CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Description</TableHead>
                  <TableHead>Users</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {roles.data.map((role) => (
                  <TableRow key={role.id}>
                    <TableCell className="font-medium">{role.name}</TableCell>
                    <TableCell>
                      <div className="max-w-[200px] truncate">
                        {role.description || 'No description'}
                      </div>
                    </TableCell>

                    <TableCell>
                      <div className="text-sm">{role.users.length} users</div>
                    </TableCell>
                    <TableCell className="text-right">
                      <div className="flex justify-end gap-2">
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/admin/roles/${role.id}`}>
                            <Eye className="h-4 w-4" />
                          </Link>
                        </Button>
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/admin/roles/${role.id}/edit`}>
                            <Edit className="h-4 w-4" />
                          </Link>
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          className="text-destructive"
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
            {roles.links.length > 3 && (
              <div className="flex items-center justify-center space-x-2 py-4">
                {roles.links.map((link, index) => (
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
