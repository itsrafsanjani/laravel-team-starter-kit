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
import { Link, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';

interface Team {
  id: string;
  name: string;
  slug: string;
  type: string;
  website?: string;
  phone?: string;
  address?: string;
  city?: string;
  state?: string;
  postal_code?: string;
  country?: string;
  created_at: string;
  owner: {
    id: string;
    name: string;
    email: string;
  };
}

interface User {
  id: string;
  name: string;
  email: string;
}

interface TeamEditProps {
  team: Team;
  users: User[];
}

export default function TeamEdit({ team, users }: TeamEditProps) {
  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Teams', href: '/admin/teams' },
    { title: team.name, href: `/admin/teams/${team.slug}` },
    { title: 'Edit', href: `/admin/teams/${team.slug}/edit` },
  ];

  const { data, setData, put, processing, errors } = useForm({
    name: team.name,
    slug: team.slug,
    type: team.type,
    user_id: team.owner.id,
    website: team.website || '',
    phone: team.phone || '',
    address: team.address || '',
    city: team.city || '',
    state: team.state || '',
    postal_code: team.postal_code || '',
    country: team.country || '',
  });

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    put(`/admin/teams/${team.slug}`);
  };

  return (
    <AdminLayout breadcrumbs={breadcrumbs}>
      <div className="space-y-6 p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Edit Team</h1>
          </div>
        </div>

        <form onSubmit={submit} className="space-y-6">
          {/* Basic Information */}
          <Card>
            <CardHeader>
              <CardTitle>Basic Information</CardTitle>
              <CardDescription>
                Update the basic details of this team
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                  <Label htmlFor="name">Team Name</Label>
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
                  <Label htmlFor="slug">Slug</Label>
                  <Input
                    id="slug"
                    value={data.slug}
                    onChange={(e) => setData('slug', e.target.value)}
                    className={errors.slug ? 'border-destructive' : ''}
                  />
                  {errors.slug && (
                    <p className="text-sm text-destructive">{errors.slug}</p>
                  )}
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="type">Team Type</Label>
                <Select
                  value={data.type}
                  onValueChange={(value) => setData('type', value)}
                >
                  <SelectTrigger
                    className={errors.type ? 'border-destructive' : ''}
                  >
                    <SelectValue placeholder="Select team type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="personal">Personal</SelectItem>
                    <SelectItem value="business">Business</SelectItem>
                  </SelectContent>
                </Select>
                {errors.type && (
                  <p className="text-sm text-destructive">{errors.type}</p>
                )}
              </div>

              <div className="space-y-2">
                <Label htmlFor="user_id">Team Owner</Label>
                <Select
                  value={data.user_id}
                  onValueChange={(value) => setData('user_id', value)}
                >
                  <SelectTrigger
                    className={errors.user_id ? 'border-destructive' : ''}
                  >
                    <SelectValue placeholder="Select team owner" />
                  </SelectTrigger>
                  <SelectContent>
                    {users.map((user) => (
                      <SelectItem key={user.id} value={user.id}>
                        {user.name} ({user.email})
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.user_id && (
                  <p className="text-sm text-destructive">{errors.user_id}</p>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Contact Information */}
          <Card>
            <CardHeader>
              <CardTitle>Contact Information</CardTitle>
              <CardDescription>
                Optional contact details for this team
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                  <Label htmlFor="website">Website</Label>
                  <Input
                    id="website"
                    value={data.website}
                    onChange={(e) => setData('website', e.target.value)}
                    className={errors.website ? 'border-destructive' : ''}
                  />
                  {errors.website && (
                    <p className="text-sm text-destructive">{errors.website}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="phone">Phone</Label>
                  <Input
                    id="phone"
                    value={data.phone}
                    onChange={(e) => setData('phone', e.target.value)}
                    className={errors.phone ? 'border-destructive' : ''}
                  />
                  {errors.phone && (
                    <p className="text-sm text-destructive">{errors.phone}</p>
                  )}
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="address">Address</Label>
                <Input
                  id="address"
                  value={data.address}
                  onChange={(e) => setData('address', e.target.value)}
                  className={errors.address ? 'border-destructive' : ''}
                />
                {errors.address && (
                  <p className="text-sm text-destructive">{errors.address}</p>
                )}
              </div>

              <div className="grid gap-4 md:grid-cols-3">
                <div className="space-y-2">
                  <Label htmlFor="city">City</Label>
                  <Input
                    id="city"
                    value={data.city}
                    onChange={(e) => setData('city', e.target.value)}
                    className={errors.city ? 'border-destructive' : ''}
                  />
                  {errors.city && (
                    <p className="text-sm text-destructive">{errors.city}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="state">State</Label>
                  <Input
                    id="state"
                    value={data.state}
                    onChange={(e) => setData('state', e.target.value)}
                    className={errors.state ? 'border-destructive' : ''}
                  />
                  {errors.state && (
                    <p className="text-sm text-destructive">{errors.state}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="postal_code">Postal Code</Label>
                  <Input
                    id="postal_code"
                    value={data.postal_code}
                    onChange={(e) => setData('postal_code', e.target.value)}
                    className={errors.postal_code ? 'border-destructive' : ''}
                  />
                  {errors.postal_code && (
                    <p className="text-sm text-destructive">
                      {errors.postal_code}
                    </p>
                  )}
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="country">Country</Label>
                <Input
                  id="country"
                  value={data.country}
                  onChange={(e) => setData('country', e.target.value)}
                  className={errors.country ? 'border-destructive' : ''}
                />
                {errors.country && (
                  <p className="text-sm text-destructive">{errors.country}</p>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Actions */}
          <div className="flex justify-end gap-4">
            <Button asChild variant="outline">
              <Link href={`/admin/teams/${team.slug}`}>Cancel</Link>
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
