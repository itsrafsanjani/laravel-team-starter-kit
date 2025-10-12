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
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin-layout';
import { Link, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';

interface User {
  id: string;
  name: string;
  email: string;
  created_at: string;
  avatar?: string;
  is_banned: boolean;
  banned_reason?: string;
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
  }>;
}

interface AdminRole {
  id: string;
  name: string;
  slug: string;
}

interface UserEditProps {
  user: User;
  availableRoles: AdminRole[];
}

export default function UserEdit({ user, availableRoles }: UserEditProps) {
  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Users', href: '/admin/users' },
    { title: user.name, href: `/admin/users/${user.id}` },
    { title: 'Edit', href: `/admin/users/${user.id}/edit` },
  ];

  const { data, setData, put, processing, errors } = useForm({
    name: user.name,
    email: user.email,
    password: '',
    password_confirmation: '',
    avatar: user.avatar || '',
    is_banned: user.is_banned,
    banned_reason: user.banned_reason || '',
  });

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    put(`/admin/users/${user.id}`);
  };

  return (
    <AdminLayout breadcrumbs={breadcrumbs}>
      <div className="space-y-6 p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Edit User</h1>
          </div>
        </div>

        <form onSubmit={submit} className="space-y-6">
          {/* Basic Information */}
          <Card>
            <CardHeader>
              <CardTitle>Basic Information</CardTitle>
              <CardDescription>
                Update the basic details of this user
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                  <Label htmlFor="name">Name</Label>
                  <Input
                    id="name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    className={errors.name ? 'border-destructive' : ''}
                  />
                  {errors.name && (
                    <p className="text-sm text-destructive">{errors.name}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="email">Email</Label>
                  <Input
                    id="email"
                    type="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    className={errors.email ? 'border-destructive' : ''}
                  />
                  {errors.email && (
                    <p className="text-sm text-destructive">{errors.email}</p>
                  )}
                </div>
              </div>

              <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                  <Label htmlFor="password">New Password</Label>
                  <Input
                    id="password"
                    type="password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    className={errors.password ? 'border-destructive' : ''}
                    placeholder="Leave blank to keep current password"
                  />
                  {errors.password && (
                    <p className="text-sm text-destructive">
                      {errors.password}
                    </p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="password_confirmation">
                    Confirm Password
                  </Label>
                  <Input
                    id="password_confirmation"
                    type="password"
                    value={data.password_confirmation}
                    onChange={(e) =>
                      setData('password_confirmation', e.target.value)
                    }
                    className={
                      errors.password_confirmation ? 'border-destructive' : ''
                    }
                    placeholder="Confirm new password"
                  />
                  {errors.password_confirmation && (
                    <p className="text-sm text-destructive">
                      {errors.password_confirmation}
                    </p>
                  )}
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="avatar">Avatar URL</Label>
                <Input
                  id="avatar"
                  value={data.avatar}
                  onChange={(e) => setData('avatar', e.target.value)}
                  className={errors.avatar ? 'border-destructive' : ''}
                  placeholder="https://example.com/avatar.jpg"
                />
                {errors.avatar && (
                  <p className="text-sm text-destructive">{errors.avatar}</p>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Ban Management */}
          <Card>
            <CardHeader>
              <CardTitle>Ban Management</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center space-x-2">
                <Switch
                  id="is_banned"
                  checked={data.is_banned}
                  onCheckedChange={(checked) => setData('is_banned', checked)}
                />
                <Label htmlFor="is_banned">Ban User</Label>
              </div>

              {data.is_banned && (
                <div className="space-y-2">
                  <Label htmlFor="banned_reason">Ban Reason</Label>
                  <Textarea
                    id="banned_reason"
                    value={data.banned_reason}
                    onChange={(e) => setData('banned_reason', e.target.value)}
                    className={errors.banned_reason ? 'border-destructive' : ''}
                    placeholder="Enter the reason for banning this user..."
                    rows={3}
                  />
                  {errors.banned_reason && (
                    <p className="text-sm text-destructive">
                      {errors.banned_reason}
                    </p>
                  )}
                </div>
              )}
            </CardContent>
          </Card>

          {/* Actions */}
          <div className="flex justify-end gap-4">
            <Button asChild variant="outline">
              <Link href={`/admin/users/${user.id}`}>Cancel</Link>
            </Button>
            <Button type="submit" disabled={processing}>
              <Save className="mr-2 h-4 w-4" />
              {processing ? 'Saving...' : 'Save Changes'}
            </Button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
