import { AdminSidebar } from '@/components/admin-sidebar';
import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

interface AdminLayoutProps {
  breadcrumbs?: BreadcrumbItem[];
}

export default function AdminLayout({
  children,
  breadcrumbs = [],
}: PropsWithChildren<AdminLayoutProps>) {
  const { adminPermissions } = usePage().props as {
    adminPermissions?: string[];
  };

  return (
    <AppShell variant="sidebar">
      <AdminSidebar permissions={adminPermissions} />
      <AppContent variant="sidebar" className="overflow-x-hidden">
        <AppSidebarHeader breadcrumbs={breadcrumbs} />
        {children}
      </AppContent>
    </AppShell>
  );
}
