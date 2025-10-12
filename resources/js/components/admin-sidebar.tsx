import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import {
  BookOpen,
  Building2,
  CreditCard,
  Folder,
  LayoutGrid,
  Shield,
  Users,
} from 'lucide-react';

interface AdminSidebarProps {
  permissions?: string[];
}

const footerNavItems: NavItem[] = [
  {
    title: 'Repository',
    href: 'https://github.com/laravel/react-starter-kit',
    icon: Folder,
  },
  {
    title: 'Documentation',
    href: 'https://laravel.com/docs/starter-kits#react',
    icon: BookOpen,
  },
];

export function AdminSidebar({ permissions }: AdminSidebarProps) {
  // Helper function to check if user has a specific permission
  const hasPermission = (permission: string) => {
    return permissions?.includes(permission) || false;
  };

  const mainNavItems: NavItem[] = [
    {
      title: 'Dashboard',
      href: '/admin',
      icon: LayoutGrid,
    },
    // Only show Users if user has manage_users permission
    ...(hasPermission('manage_users')
      ? [
          {
            title: 'Users',
            href: '/admin/users',
            icon: Users,
          },
        ]
      : []),
    // Only show Teams if user has manage_teams permission
    ...(hasPermission('manage_teams')
      ? [
          {
            title: 'Teams',
            href: '/admin/teams',
            icon: Building2,
          },
        ]
      : []),
    // Only show Admin Roles if user has manage_settings permission
    ...(hasPermission('manage_settings')
      ? [
          {
            title: 'Admin Roles',
            href: '/admin/roles',
            icon: Shield,
          },
        ]
      : []),
    // Only show Plans if user has manage_settings permission
    ...(hasPermission('manage_settings')
      ? [
          {
            title: 'Plans',
            href: '/admin/plans',
            icon: CreditCard,
          },
        ]
      : []),
  ];

  return (
    <Sidebar collapsible="icon" variant="inset">
      <SidebarHeader>
        <div className="px-2 py-2">
          <div className="flex items-center gap-2">
            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
              <Shield className="h-4 w-4" />
            </div>
            <div className="flex flex-col">
              <span className="text-sm font-semibold">Admin Panel</span>
              <span className="text-xs text-muted-foreground">Management</span>
            </div>
          </div>
        </div>
      </SidebarHeader>

      <SidebarContent>
        <NavMain items={mainNavItems} />
      </SidebarContent>

      <SidebarFooter>
        <NavFooter items={footerNavItems} className="mt-auto" />
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
}
