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
import { Link, router } from '@inertiajs/react';
import { Edit, Trash2 } from 'lucide-react';

interface Plan {
  id: number;
  name: string;
  slug: string;
  description: string;
  type: 'free' | 'trial' | 'subscription' | 'lifetime';
  monthly_price: number | null;
  yearly_price: number | null;
  lifetime_price: number | null;
  stripe_monthly_price_id: string | null;
  stripe_yearly_price_id: string | null;
  stripe_lifetime_price_id: string | null;
  trial_days: number;
  features: string[];
  permissions: Record<string, any>;
  is_active: boolean;
  is_popular: boolean;
  is_legacy: boolean;
  sort_order: number;
  created_at: string;
  updated_at: string;
  subscriptions?: Array<{
    id: number;
    team_id: number;
    status: string;
    created_at: string;
  }>;
  trials?: Array<{
    id: number;
    team_id: number;
    status: string;
    created_at: string;
  }>;
}

interface ShowPlanProps {
  plan: Plan;
}

export default function ShowPlan({ plan }: ShowPlanProps) {
  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Plans', href: '/admin/plans' },
    { title: plan.name, href: `/admin/plans/${plan.id}` },
  ];

  const formatPrice = (price: number | string | null) => {
    if (price === null || price === '') return 'N/A';
    const numPrice = typeof price === 'string' ? parseFloat(price) : price;
    if (isNaN(numPrice)) return 'N/A';
    return `$${numPrice.toFixed(2)}`;
  };

  const getTypeBadgeVariant = (type: string) => {
    switch (type) {
      case 'free':
        return 'secondary';
      case 'trial':
        return 'outline';
      case 'subscription':
        return 'default';
      case 'lifetime':
        return 'destructive';
      default:
        return 'outline';
    }
  };

  const handleDelete = () => {
    if (
      confirm(
        `Are you sure you want to delete the plan "${plan.name}"? This action cannot be undone.`,
      )
    ) {
      router.delete(`/admin/plans/${plan.id}`);
    }
  };

  return (
    <AdminLayout breadcrumbs={breadcrumbs}>
      <div className="space-y-6 p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">{plan.name}</h1>
            <p className="text-muted-foreground">
              {plan.description || 'No description provided'}
            </p>
          </div>
          <div className="flex gap-2">
            <Button asChild>
              <Link href={`/admin/plans/${plan.id}/edit`}>
                <Edit className="mr-2 h-4 w-4" />
                Edit Plan
              </Link>
            </Button>
            <Button
              variant="outline"
              className="text-destructive"
              onClick={handleDelete}
            >
              <Trash2 className="mr-2 h-4 w-4" />
              Delete
            </Button>
          </div>
        </div>

        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
          {/* Basic Information */}
          <Card>
            <CardHeader>
              <CardTitle>Basic Information</CardTitle>
              <CardDescription>Plan details and identification</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Name
                  </label>
                  <p className="text-sm">{plan.name}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Slug
                  </label>
                  <p className="font-mono text-sm">{plan.slug}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Type
                  </label>
                  <div className="mt-1">
                    <Badge variant={getTypeBadgeVariant(plan.type)}>
                      {plan.type.charAt(0).toUpperCase() + plan.type.slice(1)}
                    </Badge>
                  </div>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Sort Order
                  </label>
                  <p className="text-sm">{plan.sort_order}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Status and Settings */}
          <Card>
            <CardHeader>
              <CardTitle>Status & Settings</CardTitle>
              <CardDescription>
                Plan status and visibility settings
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Status
                  </label>
                  <div className="mt-1">
                    <Badge variant={plan.is_active ? 'default' : 'secondary'}>
                      {plan.is_active ? 'Active' : 'Inactive'}
                    </Badge>
                  </div>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Popular
                  </label>
                  <div className="mt-1">
                    <Badge variant={plan.is_popular ? 'default' : 'outline'}>
                      {plan.is_popular ? 'Yes' : 'No'}
                    </Badge>
                  </div>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Legacy
                  </label>
                  <div className="mt-1">
                    <Badge variant={plan.is_legacy ? 'destructive' : 'outline'}>
                      {plan.is_legacy ? 'Yes' : 'No'}
                    </Badge>
                  </div>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Trial Days
                  </label>
                  <p className="text-sm">{plan.trial_days} days</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Pricing Information */}
        <Card>
          <CardHeader>
            <CardTitle>Pricing</CardTitle>
            <CardDescription>
              Plan pricing for different billing cycles
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
              <div className="rounded-lg border p-4 text-center">
                <h3 className="text-lg font-semibold">Monthly</h3>
                <p className="text-2xl font-bold text-primary">
                  {formatPrice(plan.monthly_price)}
                </p>
                {plan.stripe_monthly_price_id && (
                  <p className="mt-1 text-xs text-muted-foreground">
                    ID: {plan.stripe_monthly_price_id}
                  </p>
                )}
              </div>
              <div className="rounded-lg border p-4 text-center">
                <h3 className="text-lg font-semibold">Yearly</h3>
                <p className="text-2xl font-bold text-primary">
                  {formatPrice(plan.yearly_price)}
                </p>
                {plan.stripe_yearly_price_id && (
                  <p className="mt-1 text-xs text-muted-foreground">
                    ID: {plan.stripe_yearly_price_id}
                  </p>
                )}
              </div>
              <div className="rounded-lg border p-4 text-center">
                <h3 className="text-lg font-semibold">Lifetime</h3>
                <p className="text-2xl font-bold text-primary">
                  {formatPrice(plan.lifetime_price)}
                </p>
                {plan.stripe_lifetime_price_id && (
                  <p className="mt-1 text-xs text-muted-foreground">
                    ID: {plan.stripe_lifetime_price_id}
                  </p>
                )}
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Features */}
        <Card>
          <CardHeader>
            <CardTitle>Features</CardTitle>
            <CardDescription>Features included in this plan</CardDescription>
          </CardHeader>
          <CardContent>
            {plan.features && plan.features.length > 0 ? (
              <div className="grid grid-cols-1 gap-2 md:grid-cols-2">
                {plan.features.map((feature, index) => (
                  <div key={index} className="flex items-center gap-2">
                    <div className="h-2 w-2 rounded-full bg-primary" />
                    <span className="text-sm">{feature}</span>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-muted-foreground">No features defined</p>
            )}
          </CardContent>
        </Card>

        {/* Permissions */}
        {plan.permissions && Object.keys(plan.permissions).length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle>Permissions & Limits</CardTitle>
              <CardDescription>
                Plan-specific permissions and usage limits
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div className="rounded-lg bg-muted p-4">
                  <pre className="font-mono text-sm whitespace-pre-wrap">
                    {JSON.stringify(plan.permissions, null, 2)}
                  </pre>
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Usage Statistics */}
        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
          {/* Subscriptions */}
          <Card>
            <CardHeader>
              <CardTitle>Active Subscriptions</CardTitle>
              <CardDescription>
                Teams currently subscribed to this plan
              </CardDescription>
            </CardHeader>
            <CardContent>
              {plan.subscriptions && plan.subscriptions.length > 0 ? (
                <div className="space-y-2">
                  <p className="text-2xl font-bold">
                    {plan.subscriptions.length}
                  </p>
                  <p className="text-sm text-muted-foreground">
                    Active subscriptions
                  </p>
                </div>
              ) : (
                <p className="text-muted-foreground">No active subscriptions</p>
              )}
            </CardContent>
          </Card>

          {/* Trials */}
          <Card>
            <CardHeader>
              <CardTitle>Active Trials</CardTitle>
              <CardDescription>
                Teams currently on trial for this plan
              </CardDescription>
            </CardHeader>
            <CardContent>
              {plan.trials && plan.trials.length > 0 ? (
                <div className="space-y-2">
                  <p className="text-2xl font-bold">{plan.trials.length}</p>
                  <p className="text-sm text-muted-foreground">Active trials</p>
                </div>
              ) : (
                <p className="text-muted-foreground">No active trials</p>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Metadata */}
        <Card>
          <CardHeader>
            <CardTitle>Metadata</CardTitle>
            <CardDescription>
              Plan creation and modification information
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label className="text-sm font-medium text-muted-foreground">
                  Created
                </label>
                <p className="text-sm">
                  {new Date(plan.created_at).toLocaleString()}
                </p>
              </div>
              <div>
                <label className="text-sm font-medium text-muted-foreground">
                  Last Updated
                </label>
                <p className="text-sm">
                  {new Date(plan.updated_at).toLocaleString()}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
