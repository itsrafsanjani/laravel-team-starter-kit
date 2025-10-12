import { Breadcrumbs } from '@/components/breadcrumbs';
import { AlertWithIcon } from '@/components/ui/alert';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export function AppSidebarHeader({
  breadcrumbs = [],
}: {
  breadcrumbs?: BreadcrumbItemType[];
}) {
  const [showFlashMessage, setShowFlashMessage] = useState(false);
  const { flash } = usePage().props as any;

  // Handle flash messages
  useEffect(() => {
    if (flash?.success || flash?.error) {
      setShowFlashMessage(true);
      // Auto-hide flash message after 5 seconds
      const timer = setTimeout(() => {
        setShowFlashMessage(false);
      }, 5000);
      return () => clearTimeout(timer);
    }
  }, [flash?.success, flash?.error]);

  return (
    <>
      <header className="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
        <div className="flex items-center gap-2">
          <SidebarTrigger className="-ml-1" />
          <Breadcrumbs breadcrumbs={breadcrumbs} />
        </div>
      </header>

      {/* Flash Messages */}
      {showFlashMessage && (flash?.success || flash?.error) && (
        <div className="px-6 pt-4">
          <AlertWithIcon
            variant={flash?.success ? 'success' : 'destructive'}
            description={flash?.success || flash?.error}
            onClose={() => setShowFlashMessage(false)}
          />
        </div>
      )}
    </>
  );
}
