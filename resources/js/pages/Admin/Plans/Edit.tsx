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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin-layout';
import { validateJson } from '@/utils/jsonValidation';
import { useForm } from '@inertiajs/react';
import { AlertCircle, CheckCircle, Plus, Trash2 } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

interface Plan {
  id: number;
  name: string;
  slug: string;
  description: string;
  type: 'free' | 'trial' | 'subscription' | 'lifetime';
  monthly_price: number | null | string;
  yearly_price: number | null | string;
  lifetime_price: number | null | string;
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
}

interface EditPlanProps {
  plan: Plan;
  planTypes: string[];
}

interface FormData {
  name: string;
  slug: string;
  description: string;
  type: 'free' | 'trial' | 'subscription' | 'lifetime';
  monthly_price: string;
  yearly_price: string;
  lifetime_price: string;
  stripe_monthly_price_id: string;
  stripe_yearly_price_id: string;
  stripe_lifetime_price_id: string;
  trial_days: number;
  features: string[];
  permissions: Record<string, any>;
  is_active: boolean;
  is_popular: boolean;
  is_legacy: boolean;
  sort_order: number;
}

export default function EditPlan({ plan, planTypes }: EditPlanProps) {
  const { data, setData, put, processing, errors } = useForm({
    name: plan.name,
    slug: plan.slug,
    description: plan.description || '',
    type: plan.type,
    monthly_price: plan.monthly_price?.toString() || '',
    yearly_price: plan.yearly_price?.toString() || '',
    lifetime_price: plan.lifetime_price?.toString() || '',
    stripe_monthly_price_id: plan.stripe_monthly_price_id || '',
    stripe_yearly_price_id: plan.stripe_yearly_price_id || '',
    stripe_lifetime_price_id: plan.stripe_lifetime_price_id || '',
    trial_days: plan.trial_days,
    features: plan.features || [],
    permissions: plan.permissions || {},
    is_active: plan.is_active,
    is_popular: plan.is_popular,
    is_legacy: plan.is_legacy,
    sort_order: plan.sort_order,
  });

  const [features, setFeatures] = useState(plan.features || ['']);
  const [permissionsJson, setPermissionsJson] = useState(
    JSON.stringify(plan.permissions || {}, null, 2),
  );

  // JSON validation
  const jsonValidation = useMemo(
    () => validateJson(permissionsJson),
    [permissionsJson],
  );

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Plans', href: '/admin/plans' },
    { title: plan.name, href: `/admin/plans/${plan.id}` },
    { title: 'Edit', href: `/admin/plans/${plan.id}/edit` },
  ];

  useEffect(() => {
    setFeatures(plan.features || ['']);
  }, [plan.features]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    // Prevent submission if JSON is invalid
    if (permissionsJson.trim() && !jsonValidation.isValid) {
      return;
    }

    const filteredFeatures = features.filter(
      (feature) => feature.trim() !== '',
    );

    // Parse permissions JSON
    let permissions = {};
    try {
      permissions = JSON.parse(permissionsJson);
    } catch (error) {
      // If JSON is invalid, use empty object
      permissions = {};
    }

    // Update the form data with features and permissions
    setData('features', filteredFeatures);
    setData('permissions', permissions);

    // Use Inertia's put method with the updated data
    put(`/admin/plans/${plan.id}`);
  };

  const addFeature = () => {
    setFeatures([...features, '']);
  };

  const removeFeature = (index: number) => {
    const newFeatures = features.filter((_, i) => i !== index);
    // If no features left, add one empty feature
    setFeatures(newFeatures.length === 0 ? [''] : newFeatures);
  };

  const updateFeature = (index: number, value: string) => {
    const newFeatures = [...features];
    newFeatures[index] = value;
    setFeatures(newFeatures);
  };

  const handleTypeChange = (type: string) => {
    (setData as any)('type', type);
    // Reset prices when type changes
    if (type === 'free') {
      (setData as any)('monthly_price', '');
      (setData as any)('yearly_price', '');
      (setData as any)('lifetime_price', '');
    } else if (type === 'lifetime') {
      (setData as any)('monthly_price', '');
      (setData as any)('yearly_price', '');
    }
  };

  return (
    <AdminLayout breadcrumbs={breadcrumbs}>
      <div className="space-y-6 p-4">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Edit Plan</h1>
          <p className="text-muted-foreground">
            Update plan details and settings
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {/* Basic Information */}
            <Card>
              <CardHeader>
                <CardTitle>Basic Information</CardTitle>
                <CardDescription>
                  Basic plan details and identification
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="name">Plan Name</Label>
                  <Input
                    id="name"
                    value={data.name}
                    onChange={(e) => (setData as any)('name', e.target.value)}
                    placeholder="e.g., Pro Plan"
                    className={errors.name ? 'border-red-500' : ''}
                  />
                  {errors.name && (
                    <p className="text-sm text-red-500">{errors.name}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="slug">Slug</Label>
                  <Input
                    id="slug"
                    value={data.slug}
                    onChange={(e) => (setData as any)('slug', e.target.value)}
                    placeholder="e.g., pro-plan"
                    className={errors.slug ? 'border-red-500' : ''}
                  />
                  {errors.slug && (
                    <p className="text-sm text-red-500">{errors.slug}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
                    value={data.description}
                    onChange={(e) =>
                      (setData as any)('description', e.target.value)
                    }
                    placeholder="Brief description of the plan"
                    rows={3}
                    className={errors.description ? 'border-red-500' : ''}
                  />
                  {errors.description && (
                    <p className="text-sm text-red-500">{errors.description}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="type">Plan Type</Label>
                  <Select value={data.type} onValueChange={handleTypeChange}>
                    <SelectTrigger
                      className={errors.type ? 'border-red-500' : ''}
                    >
                      <SelectValue placeholder="Select plan type" />
                    </SelectTrigger>
                    <SelectContent>
                      {planTypes.map((type) => (
                        <SelectItem key={type} value={type}>
                          {type.charAt(0).toUpperCase() + type.slice(1)}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {errors.type && (
                    <p className="text-sm text-red-500">{errors.type}</p>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Pricing Information */}
            <Card>
              <CardHeader>
                <CardTitle>Pricing</CardTitle>
                <CardDescription>
                  Set pricing for different billing cycles
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                {data.type !== 'free' && data.type !== 'lifetime' && (
                  <div className="space-y-2">
                    <Label htmlFor="monthly_price">Monthly Price ($)</Label>
                    <Input
                      id="monthly_price"
                      type="number"
                      step="0.01"
                      min="0"
                      value={data.monthly_price}
                      onChange={(e) =>
                        (setData as any)('monthly_price', e.target.value)
                      }
                      placeholder="0.00"
                      className={errors.monthly_price ? 'border-red-500' : ''}
                    />
                    {errors.monthly_price && (
                      <p className="text-sm text-red-500">
                        {errors.monthly_price}
                      </p>
                    )}
                  </div>
                )}

                {data.type !== 'free' && data.type !== 'lifetime' && (
                  <div className="space-y-2">
                    <Label htmlFor="yearly_price">Yearly Price ($)</Label>
                    <Input
                      id="yearly_price"
                      type="number"
                      step="0.01"
                      min="0"
                      value={data.yearly_price}
                      onChange={(e) =>
                        (setData as any)('yearly_price', e.target.value)
                      }
                      placeholder="0.00"
                      className={errors.yearly_price ? 'border-red-500' : ''}
                    />
                    {errors.yearly_price && (
                      <p className="text-sm text-red-500">
                        {errors.yearly_price}
                      </p>
                    )}
                  </div>
                )}

                {data.type === 'lifetime' && (
                  <div className="space-y-2">
                    <Label htmlFor="lifetime_price">Lifetime Price ($)</Label>
                    <Input
                      id="lifetime_price"
                      type="number"
                      step="0.01"
                      min="0"
                      value={data.lifetime_price}
                      onChange={(e) =>
                        (setData as any)('lifetime_price', e.target.value)
                      }
                      placeholder="0.00"
                      className={errors.lifetime_price ? 'border-red-500' : ''}
                    />
                    {errors.lifetime_price && (
                      <p className="text-sm text-red-500">
                        {errors.lifetime_price}
                      </p>
                    )}
                  </div>
                )}

                <div className="space-y-2">
                  <Label htmlFor="trial_days">Trial Days</Label>
                  <Input
                    id="trial_days"
                    type="number"
                    min="0"
                    value={data.trial_days}
                    onChange={(e) =>
                      (setData as any)(
                        'trial_days',
                        parseInt(e.target.value) || 0,
                      )
                    }
                    placeholder="0"
                    className={errors.trial_days ? 'border-red-500' : ''}
                  />
                  {errors.trial_days && (
                    <p className="text-sm text-red-500">{errors.trial_days}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="sort_order">Sort Order</Label>
                  <Input
                    id="sort_order"
                    type="number"
                    min="0"
                    value={data.sort_order}
                    onChange={(e) =>
                      (setData as any)(
                        'sort_order',
                        parseInt(e.target.value) || 0,
                      )
                    }
                    placeholder="0"
                    className={errors.sort_order ? 'border-red-500' : ''}
                  />
                  {errors.sort_order && (
                    <p className="text-sm text-red-500">{errors.sort_order}</p>
                  )}
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Stripe Integration */}
          <Card>
            <CardHeader>
              <CardTitle>Stripe Integration</CardTitle>
              <CardDescription>
                Stripe price IDs for payment processing
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {data.type !== 'free' && data.type !== 'lifetime' && (
                <div className="space-y-2">
                  <Label htmlFor="stripe_monthly_price_id">
                    Stripe Monthly Price ID
                  </Label>
                  <Input
                    id="stripe_monthly_price_id"
                    value={data.stripe_monthly_price_id}
                    onChange={(e) =>
                      (setData as any)(
                        'stripe_monthly_price_id',
                        e.target.value,
                      )
                    }
                    placeholder="price_..."
                    className={
                      errors.stripe_monthly_price_id ? 'border-red-500' : ''
                    }
                  />
                  {errors.stripe_monthly_price_id && (
                    <p className="text-sm text-red-500">
                      {errors.stripe_monthly_price_id}
                    </p>
                  )}
                </div>
              )}

              {data.type !== 'free' && data.type !== 'lifetime' && (
                <div className="space-y-2">
                  <Label htmlFor="stripe_yearly_price_id">
                    Stripe Yearly Price ID
                  </Label>
                  <Input
                    id="stripe_yearly_price_id"
                    value={data.stripe_yearly_price_id}
                    onChange={(e) =>
                      (setData as any)('stripe_yearly_price_id', e.target.value)
                    }
                    placeholder="price_..."
                    className={
                      errors.stripe_yearly_price_id ? 'border-red-500' : ''
                    }
                  />
                  {errors.stripe_yearly_price_id && (
                    <p className="text-sm text-red-500">
                      {errors.stripe_yearly_price_id}
                    </p>
                  )}
                </div>
              )}

              {data.type === 'lifetime' && (
                <div className="space-y-2">
                  <Label htmlFor="stripe_lifetime_price_id">
                    Stripe Lifetime Price ID
                  </Label>
                  <Input
                    id="stripe_lifetime_price_id"
                    value={data.stripe_lifetime_price_id}
                    onChange={(e) =>
                      (setData as any)(
                        'stripe_lifetime_price_id',
                        e.target.value,
                      )
                    }
                    placeholder="price_..."
                    className={
                      errors.stripe_lifetime_price_id ? 'border-red-500' : ''
                    }
                  />
                  {errors.stripe_lifetime_price_id && (
                    <p className="text-sm text-red-500">
                      {errors.stripe_lifetime_price_id}
                    </p>
                  )}
                </div>
              )}
            </CardContent>
          </Card>

          {/* Features */}
          <Card>
            <CardHeader>
              <CardTitle>Features</CardTitle>
              <CardDescription>
                List the features included in this plan
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {features.map((feature, index) => (
                <div key={index} className="flex gap-2">
                  <Input
                    value={feature}
                    onChange={(e) => updateFeature(index, e.target.value)}
                    placeholder="Enter feature"
                    className=""
                  />
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => removeFeature(index)}
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              ))}
              <Button
                type="button"
                variant="outline"
                onClick={addFeature}
                className="w-full"
              >
                <Plus className="mr-2 h-4 w-4" />
                Add Feature
              </Button>
            </CardContent>
          </Card>

          {/* Permissions */}
          <Card>
            <CardHeader>
              <CardTitle>Permissions & Limits</CardTitle>
              <CardDescription>
                Configure plan permissions and usage limits as JSON
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="permissions">Permissions JSON</Label>
                <div className="relative">
                  <Textarea
                    id="permissions"
                    value={permissionsJson}
                    onChange={(e) => setPermissionsJson(e.target.value)}
                    placeholder='{"max_team_members": 10, "max_projects": 25, "advanced_analytics": true, "api_access": true}'
                    rows={8}
                    className={`font-mono text-sm ${
                      permissionsJson.trim() && !jsonValidation.isValid
                        ? 'border-red-500 focus:border-red-500'
                        : permissionsJson.trim() && jsonValidation.isValid
                          ? 'border-green-500 focus:border-green-500'
                          : ''
                    }`}
                  />
                  {permissionsJson.trim() && (
                    <div className="absolute top-2 right-2">
                      {jsonValidation.isValid ? (
                        <CheckCircle className="h-4 w-4 text-green-500" />
                      ) : (
                        <AlertCircle className="h-4 w-4 text-red-500" />
                      )}
                    </div>
                  )}
                </div>
                {permissionsJson.trim() && !jsonValidation.isValid && (
                  <div className="flex items-center gap-2 text-sm text-red-600">
                    <AlertCircle className="h-4 w-4" />
                    <span>Invalid JSON: {jsonValidation.error}</span>
                  </div>
                )}
                {permissionsJson.trim() && jsonValidation.isValid && (
                  <div className="flex items-center gap-2 text-sm text-green-600">
                    <CheckCircle className="h-4 w-4" />
                    <span>Valid JSON</span>
                  </div>
                )}
                <p className="text-xs text-muted-foreground">
                  Enter permissions as valid JSON. Example:{' '}
                  {`{"max_team_members": 10, "advanced_analytics": true}`}
                </p>
              </div>
            </CardContent>
          </Card>

          {/* Settings */}
          <Card>
            <CardHeader>
              <CardTitle>Settings</CardTitle>
              <CardDescription>
                Configure plan settings and visibility
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center space-x-2">
                <Checkbox
                  id="is_active"
                  checked={data.is_active}
                  onCheckedChange={(checked) =>
                    (setData as any)('is_active', !!checked)
                  }
                />
                <Label htmlFor="is_active">Active</Label>
              </div>

              <div className="flex items-center space-x-2">
                <Checkbox
                  id="is_popular"
                  checked={data.is_popular}
                  onCheckedChange={(checked) =>
                    (setData as any)('is_popular', !!checked)
                  }
                />
                <Label htmlFor="is_popular">Popular</Label>
              </div>

              <div className="flex items-center space-x-2">
                <Checkbox
                  id="is_legacy"
                  checked={data.is_legacy}
                  onCheckedChange={(checked) =>
                    (setData as any)('is_legacy', !!checked)
                  }
                />
                <Label htmlFor="is_legacy">Legacy</Label>
              </div>
            </CardContent>
          </Card>

          {/* Submit Button */}
          <div className="flex justify-end">
            <Button
              type="submit"
              disabled={
                processing ||
                (permissionsJson.trim() !== '' && !jsonValidation.isValid)
              }
            >
              {processing ? 'Updating...' : 'Update Plan'}
            </Button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
