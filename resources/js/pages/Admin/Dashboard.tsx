import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import AdminLayout from '@/layouts/admin-layout';
import { Link } from '@inertiajs/react';
import { Building2, Shield, UserCheck, Users } from 'lucide-react';

interface AdminDashboardProps {
  stats: {
    total_users: number;
    total_teams: number;
    total_admin_roles: number;
    active_admin_users: number;
  };
  recent_users: Array<{
    id: string;
    name: string;
    email: string;
    created_at: string;
    admin_role?: Array<{
      id: string;
      name: string;
      slug: string;
    }>;
  }>;
  recent_teams: Array<{
    id: number;
    name: string;
    slug: string;
    type: string;
    created_at: string;
    owner: {
      name: string;
      email: string;
    };
  }>;
}

export default function AdminDashboard({
  stats,
  recent_users,
  recent_teams,
}: AdminDashboardProps) {
  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Dashboard', href: '/admin' },
  ];

  return (
    <AdminLayout breadcrumbs={breadcrumbs}>
      <div className="space-y-6 p-4">
        {/* Stats Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Users</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.total_users}</div>
              <p className="text-xs text-muted-foreground">
                All registered users
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Teams</CardTitle>
              <Building2 className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.total_teams}</div>
              <p className="text-xs text-muted-foreground">All teams created</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Admin Roles</CardTitle>
              <Shield className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {stats.total_admin_roles}
              </div>
              <p className="text-xs text-muted-foreground">Available roles</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">
                Active Admins
              </CardTitle>
              <UserCheck className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {stats.active_admin_users}
              </div>
              <p className="text-xs text-muted-foreground">
                Users with admin access
              </p>
            </CardContent>
          </Card>
        </div>

        <div className="grid gap-6 md:grid-cols-2">
          {/* Recent Users */}
          <Card>
            <CardHeader>
              <CardTitle>Recent Users</CardTitle>
              <CardDescription>Latest registered users</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {recent_users.map((user) => (
                  <div
                    key={user.id}
                    className="flex items-center justify-between"
                  >
                    <div className="space-y-1">
                      <p className="text-sm leading-none font-medium">
                        {user.name}
                      </p>
                      <p className="text-sm text-muted-foreground">
                        {user.email}
                      </p>
                      <div className="flex gap-1">
                        {user.admin_role &&
                          user.admin_role.map((role) => (
                            <Badge
                              key={role.id}
                              variant="secondary"
                              className="text-xs"
                            >
                              {role.name}
                            </Badge>
                          ))}
                        {(!user.admin_role || user.admin_role.length === 0) && (
                          <span className="text-xs text-muted-foreground">
                            No role
                          </span>
                        )}
                      </div>
                    </div>
                    <div className="text-xs text-muted-foreground">
                      {new Date(user.created_at).toLocaleDateString()}
                    </div>
                  </div>
                ))}
              </div>
              <div className="mt-4">
                <Button asChild variant="outline" className="w-full">
                  <Link href="/admin/users">View All Users</Link>
                </Button>
              </div>
            </CardContent>
          </Card>

          {/* Recent Teams */}
          <Card>
            <CardHeader>
              <CardTitle>Recent Teams</CardTitle>
              <CardDescription>Latest created teams</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {recent_teams.map((team) => (
                  <div
                    key={team.id}
                    className="flex items-center justify-between"
                  >
                    <div className="space-y-1">
                      <p className="text-sm leading-none font-medium">
                        {team.name}
                      </p>
                      <p className="text-sm text-muted-foreground">
                        by {team.owner?.name || 'Unknown'}
                      </p>
                      <Badge
                        variant={
                          team.type === 'personal' ? 'default' : 'secondary'
                        }
                      >
                        {team.type}
                      </Badge>
                    </div>
                    <div className="text-xs text-muted-foreground">
                      {new Date(team.created_at).toLocaleDateString()}
                    </div>
                  </div>
                ))}
              </div>
              <div className="mt-4">
                <Button asChild variant="outline" className="w-full">
                  <Link href="/admin/teams">View All Teams</Link>
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </AdminLayout>
  );
}
