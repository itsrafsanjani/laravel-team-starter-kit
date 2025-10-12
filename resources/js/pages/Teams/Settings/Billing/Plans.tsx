import AppLogoIcon from '@/components/app-logo-icon';
import { AlertWithIcon } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import teamRoutes from '@/routes/team';
import { Team } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { ChevronRight, CircleCheck } from 'lucide-react';
import { useMemo, useState } from 'react';

interface Plan {
  id: number;
  name: string;
  slug: string;
  description: string;
  type: 'free' | 'subscription' | 'lifetime';
  monthly_price: number | null | string;
  yearly_price: number | null | string;
  lifetime_price: number | null | string;
  trial_days: number;
  features: string[];
  permissions: Record<string, any>;
  is_active: boolean;
  is_popular: boolean;
  is_legacy: boolean;
  sort_order: number;
}

type Props = {
  team: Team;
  plans: Plan[];
};

function formatMoney(value: number | string | null): string {
  if (value === null || value === undefined || value === '') return '$0.00';
  const n = typeof value === 'string' ? parseFloat(value) : value;
  return new Intl.NumberFormat('en-US', {
    currency: 'USD',
  }).format(n);
}

const Plans = (props: Props) => {
  const { flash } = usePage().props as any;
  const [activeTab, setActiveTab] = useState<'monthly' | 'yearly' | 'lifetime'>(
    'monthly',
  );
  const [selectedPlan, setSelectedPlan] = useState<Plan | null>(null);

  const form = useForm<{
    plan_id: number | null;
    period: 'monthly' | 'yearly' | 'lifetime';
  }>({
    plan_id: null,
    period: 'monthly',
  });

  const groupedPlans = useMemo(() => {
    const groups: Record<'monthly' | 'yearly' | 'lifetime', Plan[]> = {
      monthly: [],
      yearly: [],
      lifetime: [],
    };
    (props.plans || []).forEach((p) => {
      if (p.monthly_price) groups.monthly.push(p);
      if (p.yearly_price) groups.yearly.push(p);
      if (p.lifetime_price) groups.lifetime.push(p);
    });
    return groups;
  }, [props.plans]);

  const currentPrice = useMemo(() => {
    if (!selectedPlan) return 0;
    if (activeTab === 'monthly') return Number(selectedPlan.monthly_price || 0);
    if (activeTab === 'yearly') return Number(selectedPlan.yearly_price || 0);
    return Number(selectedPlan.lifetime_price || 0);
  }, [selectedPlan, activeTab]);

  const handleTabChange = (v: string) => {
    const period = v as 'monthly' | 'yearly' | 'lifetime';
    setActiveTab(period);
    form.setData('period', period);
  };

  const handleSelectPlan = (plan: Plan) => {
    setSelectedPlan(plan);
    form.setData('plan_id', plan.id);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    // Use Inertia's router.post for the form submission
    // This should handle the server redirect to Stripe Checkout properly
    router.post(
      teamRoutes.settings.billing.checkout({ team: props.team.slug }).url,
      {
        plan_id: form.data.plan_id,
        period: form.data.period,
      },
    );
  };

  return (
    <div className="min-h-screen">
      <Head title={`${props.team.name} - Billing Plans`} />
      <div className="grid min-h-screen grid-cols-1 md:grid-cols-2">
        {/* Left: Logo + Plans (white background) */}
        <div className="bg-white p-6 md:p-10">
          <div className="mb-6 flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
            <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
          </div>

          <Tabs value={activeTab} onValueChange={handleTabChange}>
            <TabsList>
              <TabsTrigger value="monthly">Monthly</TabsTrigger>
              <TabsTrigger value="yearly">Yearly</TabsTrigger>

              {groupedPlans.lifetime.length > 0 && (
                <TabsTrigger value="lifetime">Lifetime</TabsTrigger>
              )}
            </TabsList>

            {(['monthly', 'yearly', 'lifetime'] as const).map((period) => (
              <TabsContent key={period} value={period}>
                <div className="mt-4 space-y-4">
                  {groupedPlans[period].map((plan) => {
                    const price =
                      period === 'monthly'
                        ? plan.monthly_price
                        : period === 'yearly'
                          ? plan.yearly_price
                          : plan.lifetime_price;
                    const isSelected = selectedPlan?.id === plan.id;
                    return (
                      <label
                        key={plan.id}
                        className={
                          'flex w-full cursor-pointer items-start justify-between rounded-lg border p-4 text-left transition hover:border-primary ' +
                          (isSelected
                            ? 'border-primary ring-2 ring-primary/20'
                            : 'border-gray-200 dark:border-gray-800')
                        }
                      >
                        <div className="flex items-start gap-3">
                          <input
                            type="radio"
                            name="plan"
                            className="mt-1 h-4 w-4 rounded-full border-gray-300"
                            checked={isSelected}
                            onChange={() => handleSelectPlan(plan)}
                          />
                          <div>
                            <div className="mb-1 font-medium">{plan.name}</div>
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                              {plan.description}
                            </p>
                            <ul className="mt-3 space-y-1 text-sm">
                              {(plan.features || []).map((f, i) => (
                                <li key={i} className="flex items-center gap-2">
                                  <CircleCheck className="h-4 w-4 text-green-500" />
                                  <span>{f}</span>
                                </li>
                              ))}
                            </ul>
                          </div>
                        </div>
                        <div className="text-right">
                          <div className="text-lg font-semibold">
                            ${formatMoney(price)}
                          </div>
                          <div className="text-xs text-gray-500">
                            {period === 'lifetime' ? 'one-time' : `/ ${period}`}
                          </div>
                        </div>
                      </label>
                    );
                  })}
                  {groupedPlans[period].length === 0 && (
                    <p className="text-sm text-gray-600 dark:text-gray-400">
                      No plans available.
                    </p>
                  )}
                </div>
              </TabsContent>
            ))}
          </Tabs>
        </div>

        {/* Right: Summary */}
        <div className="bg-gray-50 p-6 md:border-l md:border-gray-200 md:p-10">
          {/* Flash Messages */}
          {(flash?.success || flash?.error) && (
            <div className="pb-4">
              <AlertWithIcon
                variant={flash?.success ? 'success' : 'destructive'}
                title={flash?.success ? 'Success' : 'Error'}
                description={flash?.success || flash?.error}
              />
            </div>
          )}

          <form onSubmit={handleSubmit} className="sticky top-6 space-y-4">
            <h2 className="text-lg font-semibold">Summary</h2>

            <div className="flex items-center justify-between">
              <div className="text-base font-semibold text-gray-600 dark:text-gray-400">
                Plan
              </div>
            </div>
            <div className="flex items-center justify-between text-base">
              <div className="text-gray-600 dark:text-gray-400">
                {selectedPlan ? selectedPlan.name : '—'}
              </div>
              <div className="font-medium">
                <span>${formatMoney(currentPrice)}</span>
              </div>
            </div>
            <Separator />
            <div className="space-y-2 text-base">
              <div className="flex items-center justify-between">
                <h3 className="font-semibold">Subtotal</h3>
                <span>${formatMoney(currentPrice)}</span>
              </div>
              <div className="flex items-center justify-between font-semibold">
                <h3 className="font-semibold">Total</h3>
                <span>${formatMoney(currentPrice)}</span>
              </div>
              <div className="text-xs text-gray-600 dark:text-gray-400">
                {activeTab === 'lifetime'
                  ? 'One-time payment'
                  : `Billed ${activeTab}`}
              </div>
            </div>
            <Button
              type="submit"
              size="lg"
              className="mt-4 inline-flex w-full items-center"
              disabled={!selectedPlan || form.processing}
            >
              <div className="text-base">Continue to Payment</div>
              <ChevronRight className="size-4" />
            </Button>
          </form>
        </div>
      </div>
    </div>
  );
};

export default Plans;
