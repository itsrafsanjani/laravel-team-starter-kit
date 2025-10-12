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
import AppLayout from '@/layouts/app-layout';
import teams from '@/routes/teams';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useEffect } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Create Team',
    href: teams.create().url,
  },
];

export default function CreateTeam() {
  const pageProps = usePage().props as { auth?: { user?: { email?: string } } };

  const { data, setData, post, processing, errors } = useForm({
    name: '',
    slug: '',
    billing_email: pageProps.auth?.user?.email || '',
  });

  // Generate slug from team name
  useEffect(() => {
    if (data.name) {
      const generatedSlug = data.name
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-') // Replace spaces with hyphens
        .replace(/-+/g, '-') // Replace multiple hyphens with single hyphen
        .trim();
      setData('slug', generatedSlug);
    }
  }, [data.name, setData]);

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post(teams.store().url);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Create Team" />

      <div className="w-full">
        <div className="mx-auto md:max-w-2xl">
          <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div>
              <h2 className="text-2xl font-bold">Create New Team</h2>
              <p className="text-gray-600 dark:text-gray-400">
                Set up a new team for your organization
              </p>
            </div>

            <form onSubmit={submit} className="space-y-6">
              <Card>
                <CardHeader>
                  <CardTitle>Team Information</CardTitle>
                  <CardDescription>
                    Create a new team with basic information
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div>
                    <Label htmlFor="name">Team Name *</Label>
                    <Input
                      id="name"
                      type="text"
                      value={data.name}
                      onChange={(e) => setData('name', e.target.value)}
                      className="mt-1"
                      placeholder="Enter team name"
                      required
                    />
                    {errors.name && (
                      <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                    )}
                  </div>

                  <div>
                    <Label htmlFor="slug">Team Slug *</Label>
                    <Input
                      id="slug"
                      type="text"
                      value={data.slug}
                      onChange={(e) => setData('slug', e.target.value)}
                      className="mt-1"
                      placeholder="team-slug"
                      required
                    />
                    <p className="mt-1 text-sm text-gray-500">
                      This will be used in your team URL. Only lowercase
                      letters, numbers, and hyphens are allowed.
                    </p>
                    {errors.slug && (
                      <p className="mt-1 text-sm text-red-600">{errors.slug}</p>
                    )}
                  </div>

                  <div>
                    <Label htmlFor="billing_email">Billing Email *</Label>
                    <Input
                      id="billing_email"
                      type="email"
                      value={data.billing_email}
                      onChange={(e) => setData('billing_email', e.target.value)}
                      className="mt-1"
                      placeholder="billing@example.com"
                      required
                    />
                    <p className="mt-1 text-sm text-gray-500">
                      This email will be used for billing and payment
                      notifications.
                    </p>
                    {errors.billing_email && (
                      <p className="mt-1 text-sm text-red-600">
                        {errors.billing_email}
                      </p>
                    )}
                  </div>
                </CardContent>
              </Card>

              <div className="flex justify-end space-x-4">
                <Link href="/dashboard">
                  <Button variant="outline" type="button">
                    Cancel
                  </Button>
                </Link>
                <Button type="submit" disabled={processing}>
                  {processing ? 'Creating...' : 'Create Team'}
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
