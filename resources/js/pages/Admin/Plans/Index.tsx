import DeletePlanModal from '@/components/delete-plan-modal';
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
import { Link, router } from '@inertiajs/react';
import { Edit, Eye, Plus, Search, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface Plan {
  id: number;
  name: string;
  slug: string;
  description: string;
  type: 'free' | 'trial' | 'subscription' | 'lifetime';
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
  created_at: string;
  subscriptions_count?: number;
  trials_count?: number;
}

interface PlansIndexProps {
  plans: {
    data: Plan[];
    links: Array<{
      url: string | null;
      label: string;
      active: boolean;
    }>;
  };
  filters: {
    search?: string;
    type?: string;
    active?: string;
    legacy?: string;
  };
  planTypes: string[];
}

export default function PlansIndex({
  plans,
  filters,
  planTypes,
}: PlansIndexProps) {
  const [search, setSearch] = useState(filters.search || '');
  const [type, setType] = useState(filters.type || 'all');
  const [active, setActive] = useState(filters.active || 'all');
  const [legacy, setLegacy] = useState(filters.legacy || 'all');
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [planToDelete, setPlanToDelete] = useState<Plan | null>(null);

  const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Plans', href: '/admin/plans' },
  ];

  const handleSearch = () => {
    router.get(
      '/admin/plans',
      { search, type, active, legacy },
      {
        preserveState: true,
        replace: true,
      },
    );
  };

  const handleFilterChange = (filterName: string, value: string) => {
    const filterValue = value === 'all' ? '' : value;
    router.get(
      '/admin/plans',
      {
        search,
        type: filterName === 'type' ? filterValue : type,
        active: filterName === 'active' ? filterValue : active,
        legacy: filterName === 'legacy' ? filterValue : legacy,
      },
      {
        preserveState: true,
        replace: true,
      },
    );
  };

  const formatPrice = (price: number | null | string) => {
    if (price === null || price === undefined) return 'N/A';

    // Convert to number if it's a string
    const numericPrice = typeof price === 'string' ? parseFloat(price) : price;

    // Check if it's a valid number
    if (isNaN(numericPrice)) return 'N/A';

    return `$${numericPrice.toFixed(2)}`;
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

  const handleDelete = (plan: Plan) => {
    setPlanToDelete(plan);
    setDeleteModalOpen(true);
  };

  const handleDeleteModalClose = () => {
    setDeleteModalOpen(false);
    setPlanToDelete(null);
  };

  return (
    <AdminLayout breadcrumbs={breadcrumbs}>
      <div className="space-y-6 p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Plans</h1>
          </div>
          <Button asChild>
            <Link href="/admin/plans/create">
              <Plus className="mr-2 h-4 w-4" />
              Create Plan
            </Link>
          </Button>
        </div>

        {/* Filters */}
        <Card>
          <CardHeader>
            <CardTitle>Filters</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 gap-4 md:grid-cols-5">
              <div className="md:col-span-2">
                <div className="relative">
                  <Search className="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search plans..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                    className="pl-8"
                  />
                </div>
              </div>
              <Select
                value={type}
                onValueChange={(value) => {
                  setType(value);
                  handleFilterChange('type', value);
                }}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Filter by type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Types</SelectItem>
                  {planTypes.map((planType) => (
                    <SelectItem key={planType} value={planType}>
                      {planType.charAt(0).toUpperCase() + planType.slice(1)}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <Select
                value={active}
                onValueChange={(value) => {
                  setActive(value);
                  handleFilterChange('active', value);
                }}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="1">Active</SelectItem>
                  <SelectItem value="0">Inactive</SelectItem>
                </SelectContent>
              </Select>
              <Select
                value={legacy}
                onValueChange={(value) => {
                  setLegacy(value);
                  handleFilterChange('legacy', value);
                }}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Filter by legacy" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Plans</SelectItem>
                  <SelectItem value="0">Current</SelectItem>
                  <SelectItem value="1">Legacy</SelectItem>
                </SelectContent>
              </Select>
              <Button onClick={handleSearch}>Search</Button>
            </div>
          </CardContent>
        </Card>

        {/* Plans Table */}
        <Card>
          <CardHeader>
            <CardTitle>All Plans</CardTitle>
            <CardDescription>{plans.data.length} plans found</CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Pricing</TableHead>
                  <TableHead>Features</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Sort Order</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {plans.data.map((plan) => (
                  <TableRow key={plan.id}>
                    <TableCell className="font-medium">
                      <div className="flex items-center gap-2">
                        {plan.name}
                        {plan.is_popular && (
                          <Badge variant="default" className="text-xs">
                            Popular
                          </Badge>
                        )}
                        {plan.is_legacy && (
                          <Badge variant="outline" className="text-xs">
                            Legacy
                          </Badge>
                        )}
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge variant={getTypeBadgeVariant(plan.type)}>
                        {plan.type.charAt(0).toUpperCase() + plan.type.slice(1)}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div className="space-y-1 text-sm">
                        {plan.monthly_price && (
                          <div>Monthly: {formatPrice(plan.monthly_price)}</div>
                        )}
                        {plan.yearly_price && (
                          <div>Yearly: {formatPrice(plan.yearly_price)}</div>
                        )}
                        {plan.lifetime_price && (
                          <div>
                            Lifetime: {formatPrice(plan.lifetime_price)}
                          </div>
                        )}
                        {plan.type === 'free' && (
                          <div className="text-muted-foreground">Free</div>
                        )}
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="max-w-[200px]">
                        <div className="text-sm">
                          {plan.features?.length || 0} features
                        </div>
                        {plan.trial_days > 0 && (
                          <div className="text-xs text-muted-foreground">
                            {plan.trial_days} day trial
                          </div>
                        )}
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge variant={plan.is_active ? 'default' : 'secondary'}>
                        {plan.is_active ? 'Active' : 'Inactive'}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div className="text-sm">{plan.sort_order}</div>
                    </TableCell>
                    <TableCell className="text-right">
                      <div className="flex justify-end gap-2">
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/admin/plans/${plan.id}`}>
                            <Eye className="h-4 w-4" />
                          </Link>
                        </Button>
                        <Button asChild variant="outline" size="sm">
                          <Link href={`/admin/plans/${plan.id}/edit`}>
                            <Edit className="h-4 w-4" />
                          </Link>
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          className="text-destructive"
                          onClick={() => handleDelete(plan)}
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
            {plans.links.length > 3 && (
              <div className="flex items-center justify-center space-x-2 py-4">
                {plans.links.map((link, index) => (
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

      <DeletePlanModal
        plan={planToDelete}
        isOpen={deleteModalOpen}
        onClose={handleDeleteModalClose}
      />
    </AdminLayout>
  );
}
