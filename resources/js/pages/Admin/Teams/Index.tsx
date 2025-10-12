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
import { Link, router, useForm } from '@inertiajs/react';
import { Edit, Eye, Search, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface Team {
  id: string;
  name: string;
  slug: string;
  type: string;
  created_at: string;
  logo?: string;
  owner: {
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
}

interface TeamsIndexProps {
  teams: {
    data: Team[];
    links: Array<{
      url: string | null;
      label: string;
      active: boolean;
    }>;
  };
  filters: {
    search?: string;
    type?: string;
  };
}

export default function TeamsIndex({ teams, filters }: TeamsIndexProps) {
  const [search, setSearch] = useState(filters.search || '');
  const [type, setType] = useState(filters.type || 'all');

  const { delete: deleteTeam, processing } = useForm();

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Teams', href: '/admin/teams' },
  ];

  const handleSearch = () => {
    router.get(
      '/admin/teams',
      { search, type },
      {
        preserveState: true,
        replace: true,
      },
    );
  };

  const handleTypeFilter = (value: string) => {
    setType(value);
    const typeParam = value === 'all' ? '' : value;
    router.get(
      '/admin/teams',
      { search, type: typeParam },
      {
        preserveState: true,
        replace: true,
      },
    );
  };

  const handleDelete = (teamSlug: string) => {
    if (
      confirm(
        'Are you sure you want to delete this team? This action cannot be undone.',
      )
    ) {
      deleteTeam(`/admin/teams/${teamSlug}`, {
        onSuccess: () => {
          // The page will refresh automatically due to Inertia
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
            <h1 className="text-3xl font-bold tracking-tight">Teams</h1>
            <p className="text-muted-foreground">
              Manage all teams and their members
            </p>
          </div>
        </div>

        {/* Filters */}
        <Card>
          <CardHeader>
            <CardTitle>Filters</CardTitle>
            <CardDescription>
              Filter teams by name, slug, or type
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex gap-4">
              <div className="flex-1">
                <div className="relative">
                  <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search teams..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                    className="pl-8"
                  />
                </div>
              </div>
              <Select value={type} onValueChange={handleTypeFilter}>
                <SelectTrigger className="w-[200px]">
                  <SelectValue placeholder="Filter by type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Types</SelectItem>
                  <SelectItem value="personal">Personal</SelectItem>
                  <SelectItem value="business">Business</SelectItem>
                </SelectContent>
              </Select>
              <Button onClick={handleSearch}>Search</Button>
            </div>
          </CardContent>
        </Card>

        {/* Teams Table */}
        <Card>
          <CardHeader>
            <CardTitle>All Teams</CardTitle>
            <CardDescription>{teams.data.length} teams found</CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Team</TableHead>
                  <TableHead>Owner</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Members</TableHead>
                  <TableHead>Created</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {teams.data.map((team) => (
                  <TableRow key={team.id}>
                    <TableCell>
                      <div className="flex items-center gap-3">
                        <img
                          src={team.logo}
                          alt={`${team.name}'s logo`}
                          className="h-8 w-8 rounded-full object-cover"
                        />
                        <span className="font-medium">{team.name}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div>
                        <div className="font-medium">{team.owner.name}</div>
                        <div className="text-sm text-muted-foreground">
                          {team.owner.email}
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge
                        variant={
                          team.type === 'personal' ? 'default' : 'secondary'
                        }
                      >
                        {team.type}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div className="text-sm">{team.users.length} members</div>
                    </TableCell>
                    <TableCell>
                      {new Date(team.created_at).toLocaleDateString()}
                    </TableCell>
                    <TableCell className="text-right">
                      <div className="flex justify-end gap-2">
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/admin/teams/${team.slug}`}>
                            <Eye className="h-4 w-4" />
                          </Link>
                        </Button>
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/admin/teams/${team.slug}/edit`}>
                            <Edit className="h-4 w-4" />
                          </Link>
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          className="text-destructive"
                          onClick={() => handleDelete(team.slug)}
                          disabled={processing}
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
            {teams.links.length > 3 && (
              <div className="flex items-center justify-center space-x-2 py-4">
                {teams.links.map((link, index) => (
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
