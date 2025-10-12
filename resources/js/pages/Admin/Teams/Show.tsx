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
import { Link, router, useForm } from '@inertiajs/react';
import { Building2, Calendar, Edit, Trash2 } from 'lucide-react';

interface Team {
  id: string;
  name: string;
  slug: string;
  type: string;
  created_at: string;
  owner: {
    id: string;
    name: string;
    email: string;
  };
  users: Array<{
    id: string;
    name: string;
    email: string;
    pivot: {
      role: string;
      joined_at: string;
    };
  }>;
  subscription?: {
    id: string;
    status: string;
    plan_name: string;
    current_period_end: string;
  };
  trial?: {
    id: string;
    ends_at: string;
    is_active: boolean;
  };
}

interface TeamShowProps {
  team: Team;
}

export default function TeamShow({ team }: TeamShowProps) {
  const { delete: deleteTeam, processing } = useForm();

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Teams', href: '/admin/teams' },
    { title: team.name, href: `/admin/teams/${team.slug}` },
  ];

  const handleDelete = () => {
    if (
      confirm(
        'Are you sure you want to delete this team? This action cannot be undone.',
      )
    ) {
      deleteTeam(`/admin/teams/${team.slug}`, {
        onSuccess: () => {
          router.visit('/admin/teams');
        },
      });
    }
  };

  return (
    <AdminLayout breadcrumbs={breadcrumbs}>
      <div className="space-y-6 p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <div>
              <h1 className="text-3xl font-bold tracking-tight">{team.name}</h1>
              <p className="text-muted-foreground">/{team.slug}</p>
            </div>
          </div>
          <div className="flex gap-2">
            <Button asChild variant="outline">
              <Link href={`/admin/teams/${team.slug}/edit`}>
                <Edit className="mr-2 h-4 w-4" />
                Edit Team
              </Link>
            </Button>
            <Button
              variant="destructive"
              onClick={handleDelete}
              disabled={processing}
            >
              <Trash2 className="mr-2 h-4 w-4" />
              {processing ? 'Deleting...' : 'Delete Team'}
            </Button>
          </div>
        </div>

        <div className="grid gap-6 md:grid-cols-2">
          {/* Team Details */}
          <Card>
            <CardHeader>
              <CardTitle>Team Details</CardTitle>
              <CardDescription>
                Basic information about this team
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center gap-2">
                <Building2 className="h-4 w-4 text-muted-foreground" />
                <span className="font-medium">{team.name}</span>
                <Badge
                  variant={team.type === 'personal' ? 'default' : 'secondary'}
                >
                  {team.type}
                </Badge>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">
                  Slug: /{team.slug}
                </p>
              </div>
              <div className="flex items-center gap-2">
                <Calendar className="h-4 w-4 text-muted-foreground" />
                <span className="text-sm text-muted-foreground">
                  Created: {new Date(team.created_at).toLocaleDateString()}
                </span>
              </div>
            </CardContent>
          </Card>

          {/* Owner Information */}
          <Card>
            <CardHeader>
              <CardTitle>Team Owner</CardTitle>
              <CardDescription>The user who owns this team</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                <div className="font-medium">{team.owner.name}</div>
                <div className="text-sm text-muted-foreground">
                  {team.owner.email}
                </div>
                <Button asChild variant="outline" size="sm">
                  <Link href={`/admin/users/${team.owner.id}`}>
                    View User Profile
                  </Link>
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Subscription Information */}
        {(team.subscription || team.trial) && (
          <Card>
            <CardHeader>
              <CardTitle>Subscription</CardTitle>
              <CardDescription>
                Current subscription and billing information
              </CardDescription>
            </CardHeader>
            <CardContent>
              {team.subscription && (
                <div className="space-y-2">
                  <div className="flex items-center justify-between">
                    <span className="font-medium">Plan</span>
                    <Badge variant="outline">
                      {team.subscription.plan_name}
                    </Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="font-medium">Status</span>
                    <Badge
                      variant={
                        team.subscription.status === 'active'
                          ? 'default'
                          : 'secondary'
                      }
                    >
                      {team.subscription.status}
                    </Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="font-medium">Current Period End</span>
                    <span className="text-sm text-muted-foreground">
                      {new Date(
                        team.subscription.current_period_end,
                      ).toLocaleDateString()}
                    </span>
                  </div>
                </div>
              )}
              {team.trial && (
                <div className="space-y-2">
                  <div className="flex items-center justify-between">
                    <span className="font-medium">Trial Status</span>
                    <Badge
                      variant={team.trial.is_active ? 'default' : 'secondary'}
                    >
                      {team.trial.is_active ? 'Active' : 'Expired'}
                    </Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="font-medium">Trial Ends</span>
                    <span className="text-sm text-muted-foreground">
                      {new Date(team.trial.ends_at).toLocaleDateString()}
                    </span>
                  </div>
                </div>
              )}
            </CardContent>
          </Card>
        )}

        {/* Team Members */}
        <Card>
          <CardHeader>
            <CardTitle>Team Members</CardTitle>
            <CardDescription>
              All members of this team ({team.users.length + 1} total)
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead>Role</TableHead>
                  <TableHead>Joined</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {/* Members */}
                {team.users.map((user) => (
                  <TableRow key={user.id}>
                    <TableCell className="font-medium">{user.name}</TableCell>
                    <TableCell>{user.email}</TableCell>
                    <TableCell>
                      <Badge variant="secondary">{user.pivot.role}</Badge>
                    </TableCell>
                    <TableCell>
                      {new Date(user.pivot.joined_at).toLocaleDateString()}
                    </TableCell>
                    <TableCell className="text-right">
                      <Button asChild variant="outline" size="sm">
                        <Link href={`/admin/users/${user.id}`}>
                          View Profile
                        </Link>
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
