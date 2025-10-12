import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin-layout';
import { Link, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';

const availablePermissions = [
  {
    key: 'access_admin_panel',
    label: 'Access Admin Panel',
    description: 'Can access the admin panel',
  },
  {
    key: 'view_analytics',
    label: 'View Analytics',
    description: 'Can view analytics and reports',
  },
  {
    key: 'manage_users',
    label: 'Manage Users',
    description: 'Can create, edit, and delete users',
  },
  {
    key: 'manage_teams',
    label: 'Manage Teams',
    description: 'Can create, edit, and delete teams',
  },
  {
    key: 'manage_plans',
    label: 'Manage Plans',
    description: 'Can manage subscription plans',
  },
  {
    key: 'view_reports',
    label: 'View Reports',
    description: 'Can view detailed reports',
  },
  {
    key: 'manage_billing',
    label: 'Manage Billing',
    description: 'Can manage billing and payments',
  },
  {
    key: 'manage_system',
    label: 'Manage System',
    description: 'Can manage system settings',
  },
];

interface AdminRole {
  id: number;
  name: string;
  slug: string;
  description: string;
  permissions: string[];
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

interface EditRoleProps {
  role: AdminRole;
}

export default function EditRole({ role }: EditRoleProps) {
  const { data, setData, put, processing, errors } = useForm({
    name: role.name,
    slug: role.slug,
    description: role.description,
    permissions: role.permissions,
    is_active: role.is_active,
  });

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Admin Roles', href: '/admin/roles' },
    { title: role.name, href: `/admin/roles/${role.id}` },
    { title: 'Edit Role', href: `/admin/roles/${role.id}/edit` },
  ];

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(`/admin/roles/${role.id}`);
  };

  const handlePermissionChange = (permission: string, checked: boolean) => {
    if (checked) {
      setData('permissions', [...data.permissions, permission]);
    } else {
      setData(
        'permissions',
        data.permissions.filter((p) => p !== permission),
      );
    }
  };

  return (
    <AdminLayout breadcrumbs={breadcrumbs}>
      <div className="space-y-6 p-4">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Edit Admin Role</h1>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid gap-6 md:grid-cols-2">
            {/* Basic Information */}
            <Card>
              <CardHeader>
                <CardTitle>Basic Information</CardTitle>
                <CardDescription>
                  Basic details about the admin role
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <Label htmlFor="name">Name</Label>
                  <Input
                    id="name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    placeholder="e.g., Super Admin"
                    className={errors.name ? 'border-red-500' : ''}
                  />
                  {errors.name && (
                    <p className="mt-1 text-sm text-red-500">{errors.name}</p>
                  )}
                </div>

                <div>
                  <Label htmlFor="slug">Slug</Label>
                  <Input
                    id="slug"
                    value={data.slug}
                    onChange={(e) => setData('slug', e.target.value)}
                    placeholder="e.g., super-admin"
                    className={errors.slug ? 'border-red-500' : ''}
                  />
                  {errors.slug && (
                    <p className="mt-1 text-sm text-red-500">{errors.slug}</p>
                  )}
                  <p className="mt-1 text-xs text-muted-foreground">
                    Used internally for identification
                  </p>
                </div>

                <div>
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    placeholder="Describe what this role can do..."
                    className={errors.description ? 'border-red-500' : ''}
                  />
                  {errors.description && (
                    <p className="mt-1 text-sm text-red-500">
                      {errors.description}
                    </p>
                  )}
                </div>

                <div className="flex items-center space-x-2">
                  <Checkbox
                    id="is_active"
                    checked={data.is_active}
                    onCheckedChange={(checked) =>
                      setData('is_active', checked as boolean)
                    }
                  />
                  <Label htmlFor="is_active">Active</Label>
                </div>
              </CardContent>
            </Card>

            {/* Permissions */}
            <Card>
              <CardHeader>
                <CardTitle>Permissions</CardTitle>
                <CardDescription>
                  Select the permissions for this role
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {availablePermissions.map((permission) => (
                    <div
                      key={permission.key}
                      className="flex items-start space-x-3"
                    >
                      <Checkbox
                        id={permission.key}
                        checked={data.permissions.includes(permission.key)}
                        onCheckedChange={(checked) =>
                          handlePermissionChange(
                            permission.key,
                            checked as boolean,
                          )
                        }
                      />
                      <div className="space-y-1">
                        <Label
                          htmlFor={permission.key}
                          className="text-sm font-medium"
                        >
                          {permission.label}
                        </Label>
                        <p className="text-xs text-muted-foreground">
                          {permission.description}
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
                {errors.permissions && (
                  <p className="mt-2 text-sm text-red-500">
                    {errors.permissions}
                  </p>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Actions */}
          <div className="flex justify-end gap-4">
            <Button asChild variant="outline">
              <Link href={`/admin/roles/${role.id}`}>Cancel</Link>
            </Button>
            <Button type="submit" disabled={processing}>
              <Save className="mr-2 h-4 w-4" />
              {processing ? 'Updating...' : 'Update Role'}
            </Button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
