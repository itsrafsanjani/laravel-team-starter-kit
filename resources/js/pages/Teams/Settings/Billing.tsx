import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import TeamSettingsLayout from '@/layouts/teams/layout';
import teamRoutes from '@/routes/team';
import {
  type BreadcrumbItem,
  type Plan,
  type Subscription,
  type Team,
} from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Building, CircleCheck, User } from 'lucide-react';
import { useState } from 'react';

interface Props {
  team: Team;
  plan: Plan | null;
  planCycle: string | null;
  subscription: Subscription | null;
}

export default function TeamBillingSettings({
  team,
  plan,
  planCycle,
  subscription,
}: Props) {
  const [isAddressModalOpen, setIsAddressModalOpen] = useState(false);

  const billingForm = useForm({
    billing_type: team.billing_type || 'person',
    billing_name: team.billing_name || team.name,
    billing_email: team.billing_email || '',
    tax_id: team.tax_id || '',
  });

  const addressForm = useForm({
    address: team.address || '',
    city: team.city || '',
    state: team.state || '',
    postal_code: team.postal_code || '',
    country: team.country || '',
  });

  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Teams',
      href: teamRoutes.dashboard({ team: team.slug }).url,
    },
    {
      title: 'Team Settings',
      href: '',
    },
  ];

  const handleBillingSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    billingForm.post(
      teamRoutes.settings.billing.update({ team: team.slug }).url,
      {
        onSuccess: () => {
          // Handle success
        },
      },
    );
  };

  const handleAddressSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    e.stopPropagation(); // Prevent event from bubbling up to parent form
    addressForm.post(
      teamRoutes.settings.billing.address.update({ team: team.slug }).url,
      {
        onSuccess: () => {
          // Reset the form data
          addressForm.reset();
          // Close the modal
          setIsAddressModalOpen(false);
        },
      },
    );
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${team.name} - Billing`} />

      <TeamSettingsLayout team={team}>
        <div className="space-y-8">
          <div>
            <h3 className="text-lg font-medium">Billing</h3>
            <p className="text-sm text-gray-600 dark:text-gray-400">
              Manage your billing details and subscription.
            </p>
          </div>

          {/* Plan Overview Section */}
          <Card>
            <CardContent className="">
              <div className="space-y-6">
                {/* Plan Overview */}
                <div className="flex items-start justify-between">
                  <div className="block space-x-3">
                    <h3 className="text-lg font-semibold">Plan Overview</h3>
                  </div>
                  <div className="text-right">
                    {plan ? (
                      <div className="text-left">
                        <div className="flex items-center gap-2">
                          <span className="text-lg font-bold">{plan.name}</span>

                          <div className="inline-flex rounded-full bg-green-100 px-2 py-0.5 dark:bg-green-900">
                            <span className="text-xs font-medium text-green-700 dark:text-green-400">
                              Active
                            </span>
                          </div>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                          {plan.description}
                        </p>
                        {plan.features && plan.features.length > 0 && (
                          <div className="mt-3 space-y-1">
                            {plan.features.slice(0, 4).map((feature, index) => (
                              <div
                                key={index}
                                className="flex items-center text-sm"
                              >
                                <div className="mr-2 flex h-5 w-5 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                                  <CircleCheck className="h-4 w-4 text-green-600 dark:text-green-400" />
                                </div>
                                <span className="text-gray-700 dark:text-gray-300">
                                  {feature}
                                </span>
                              </div>
                            ))}
                          </div>
                        )}
                      </div>
                    ) : (
                      <div>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                          No active plan
                        </p>
                        <Button asChild={true} size="sm" className="mt-2">
                          <Link
                            href={
                              teamRoutes.settings.billing.plans({
                                team: team.slug,
                              }).url
                            }
                          >
                            Select Plan
                          </Link>
                        </Button>
                      </div>
                    )}
                  </div>
                </div>

                {/* Payment Method */}
                <div className="flex items-center justify-between border-t pt-4">
                  <div className="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Payment Method
                  </div>
                  <div className="flex items-center space-x-2">
                    {team.pm_type ? (
                      <>
                        <div className="flex h-6 w-8 items-center justify-center rounded bg-blue-600 text-xs font-bold text-white">
                          {team.pm_type.toUpperCase()}
                        </div>
                        <span className="text-sm text-gray-600 dark:text-gray-400">
                          .... {team.pm_last_four}
                        </span>
                      </>
                    ) : (
                      <span className="text-sm text-gray-500 dark:text-gray-500">
                        No payment method on file
                      </span>
                    )}
                  </div>
                </div>
                <div className="flex items-center justify-between border-t pt-4">
                  <div>&nbsp;</div>
                  <div>
                    <a
                      href={
                        teamRoutes.settings.billing.portal({ team: team.slug })
                          .url
                      }
                    >
                      <Button variant="outline">Manage Billing</Button>
                    </a>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Billing Details Section */}
          <Card>
            <CardHeader>
              <CardTitle>Billing Information</CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleBillingSubmit} className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="billing_type">Bill as</Label>
                  <Select
                    value={billingForm.data.billing_type}
                    onValueChange={(value: 'person' | 'company') =>
                      billingForm.setData('billing_type', value)
                    }
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="person">
                        <div className="flex items-center">
                          <User className="mr-2 h-4 w-4" />
                          Person
                        </div>
                      </SelectItem>
                      <SelectItem value="company">
                        <div className="flex items-center">
                          <Building className="mr-2 h-4 w-4" />
                          Company
                        </div>
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  {billingForm.errors.billing_type && (
                    <p className="text-sm text-red-600">
                      {billingForm.errors.billing_type}
                    </p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="billing_name">
                    {billingForm.data.billing_type === 'company'
                      ? 'Company name'
                      : 'Billing name'}
                  </Label>
                  <Input
                    id="billing_name"
                    type="text"
                    value={billingForm.data.billing_name}
                    onChange={(e) =>
                      billingForm.setData('billing_name', e.target.value)
                    }
                    placeholder={
                      billingForm.data.billing_type === 'company'
                        ? 'Enter company name'
                        : 'Enter billing name'
                    }
                  />
                  {billingForm.errors.billing_name && (
                    <p className="text-sm text-red-600">
                      {billingForm.errors.billing_name}
                    </p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="billing_email">Billing email</Label>
                  <Input
                    id="billing_email"
                    type="email"
                    value={billingForm.data.billing_email}
                    onChange={(e) =>
                      billingForm.setData('billing_email', e.target.value)
                    }
                    placeholder="Enter billing email"
                  />
                  {billingForm.errors.billing_email && (
                    <p className="text-sm text-red-600">
                      {billingForm.errors.billing_email}
                    </p>
                  )}
                </div>

                {billingForm.data.billing_type === 'company' && (
                  <div className="space-y-2">
                    <Label htmlFor="tax_id">Tax ID</Label>
                    <Input
                      id="tax_id"
                      type="text"
                      value={billingForm.data.tax_id}
                      onChange={(e) =>
                        billingForm.setData('tax_id', e.target.value)
                      }
                      placeholder="Enter tax ID"
                    />
                    {billingForm.errors.tax_id && (
                      <p className="text-sm text-red-600">
                        {billingForm.errors.tax_id}
                      </p>
                    )}
                  </div>
                )}

                <div className="space-y-2">
                  <Label className="mb-2 block">Billing address</Label>
                  <div className="flex items-center justify-between rounded-md border bg-gray-50 p-3 dark:bg-gray-800">
                    <span className="text-sm text-gray-600 dark:text-gray-400">
                      {team.address
                        ? `${team.address}, ${team.city}, ${team.state} ${team.postal_code}`
                        : 'No billing address on file'}
                    </span>
                    <Dialog
                      open={isAddressModalOpen}
                      onOpenChange={setIsAddressModalOpen}
                    >
                      <DialogTrigger asChild>
                        <Button variant="outline" size="sm">
                          {team.address ? 'Update' : 'Add'} Address
                        </Button>
                      </DialogTrigger>
                      <DialogContent className="max-w-2xl">
                        <DialogHeader>
                          <DialogTitle>Billing Address</DialogTitle>
                          <DialogDescription>
                            Enter your billing address for tax calculation.
                          </DialogDescription>
                        </DialogHeader>
                        <form
                          onSubmit={handleAddressSubmit}
                          className="space-y-6"
                        >
                          <div className="space-y-2">
                            <Label htmlFor="address">Street Address</Label>
                            <Input
                              id="address"
                              type="text"
                              value={addressForm.data.address}
                              onChange={(e) =>
                                addressForm.setData('address', e.target.value)
                              }
                              placeholder="123 Main Street"
                            />
                            {addressForm.errors.address && (
                              <p className="text-sm text-red-600">
                                {addressForm.errors.address}
                              </p>
                            )}
                          </div>

                          <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                              <Label htmlFor="city">City</Label>
                              <Input
                                id="city"
                                type="text"
                                value={addressForm.data.city}
                                onChange={(e) =>
                                  addressForm.setData('city', e.target.value)
                                }
                                placeholder="New York"
                              />
                              {addressForm.errors.city && (
                                <p className="text-sm text-red-600">
                                  {addressForm.errors.city}
                                </p>
                              )}
                            </div>

                            <div className="space-y-2">
                              <Label htmlFor="state">State/Province</Label>
                              <Input
                                id="state"
                                type="text"
                                value={addressForm.data.state}
                                onChange={(e) =>
                                  addressForm.setData('state', e.target.value)
                                }
                                placeholder="NY"
                              />
                              {addressForm.errors.state && (
                                <p className="text-sm text-red-600">
                                  {addressForm.errors.state}
                                </p>
                              )}
                            </div>
                          </div>

                          <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                              <Label htmlFor="postal_code">Postal Code</Label>
                              <Input
                                id="postal_code"
                                type="text"
                                value={addressForm.data.postal_code}
                                onChange={(e) =>
                                  addressForm.setData(
                                    'postal_code',
                                    e.target.value,
                                  )
                                }
                                placeholder="10001"
                              />
                              {addressForm.errors.postal_code && (
                                <p className="text-sm text-red-600">
                                  {addressForm.errors.postal_code}
                                </p>
                              )}
                            </div>

                            <div className="space-y-2">
                              <Label htmlFor="country">Country</Label>
                              <Input
                                id="country"
                                type="text"
                                value={addressForm.data.country}
                                onChange={(e) =>
                                  addressForm.setData('country', e.target.value)
                                }
                                placeholder="United States"
                              />
                              {addressForm.errors.country && (
                                <p className="text-sm text-red-600">
                                  {addressForm.errors.country}
                                </p>
                              )}
                            </div>
                          </div>

                          <DialogFooter>
                            <Button
                              type="button"
                              variant="outline"
                              onClick={(e) => {
                                e.stopPropagation();
                                setIsAddressModalOpen(false);
                              }}
                            >
                              Cancel
                            </Button>
                            <Button
                              type="submit"
                              disabled={addressForm.processing}
                              onClick={(e) => e.stopPropagation()}
                            >
                              {addressForm.processing
                                ? 'Saving...'
                                : 'Save Address'}
                            </Button>
                          </DialogFooter>
                        </form>
                      </DialogContent>
                    </Dialog>
                  </div>
                </div>

                <div className="flex justify-end">
                  <Button type="submit" disabled={billingForm.processing}>
                    {billingForm.processing ? 'Saving...' : 'Save Changes'}
                  </Button>
                </div>
              </form>
            </CardContent>
          </Card>
        </div>
      </TeamSettingsLayout>
    </AppLayout>
  );
}
