import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
  user: User;
}

export interface BreadcrumbItem {
  title: string;
  href: string;
}

export interface NavGroup {
  title: string;
  items: NavItem[];
}

export interface NavItem {
  title: string;
  href: NonNullable<InertiaLinkProps['href']>;
  icon?: LucideIcon | null;
  isActive?: boolean;
}

export interface Team {
  id: string;
  name: string;
  slug: string;
  type: 'personal' | 'company';
  logo?: string;
  description?: string;
  billing_email?: string;
  billing_name?: string;
  billing_type?: 'person' | 'company';
  tax_id?: string;
  address?: string;
  city?: string;
  state?: string;
  postal_code?: string;
  country?: string;
  pm_type?: string;
  pm_last_four?: string;
  users: User[];
  invitations: TeamInvitation[];
}

export interface TeamInvitation {
  id: string;
  email: string;
  role: string;
  created_at: string;
  expires_at: string;
  team?: {
    id: string;
    name: string;
  };
}

export interface Invitation {
  id: string;
  email: string;
  role: string;
  team?: Team;
}

export interface SharedData {
  name: string;
  quote: { message: string; author: string };
  auth: Auth;
  currentTeam?: Team;
  teams: Team[];
  permissions: string[];
  sidebarOpen: boolean;
  flash: {
    success?: string;
    error?: string;
  };
  [key: string]: unknown;
}

export interface Plan {
  id: number;
  name: string;
  slug: string;
  description?: string;
  type: string;
  monthly_price: number;
  yearly_price: number;
  lifetime_price: number;
  features: string[];
  permissions: Record<string, any>;
  is_active: boolean;
  is_popular: boolean;
  is_legacy: boolean;
  sort_order: number;
  trial_days: number;
}

export interface Subscription {
  id: string;
  stripe_id: string;
  stripe_status: string;
  stripe_price: string;
  quantity: number;
  trial_ends_at: string | null;
  ends_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  email_verified_at: string | null;
  two_factor_enabled?: boolean;
  created_at: string;
  updated_at: string;
  pivot?: {
    role: string;
    joined_at: string;
  };
  [key: string]: unknown; // This allows for additional properties...
}
