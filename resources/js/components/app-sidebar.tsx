import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import TeamSwitcher from '@/components/team-switcher';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  useSidebar,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import team from '@/routes/team';
import { type NavItem, type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, Settings } from 'lucide-react';

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

export function AppSidebar() {
  const { currentTeam, teams, permissions } = usePage<SharedData>().props;
  const { state } = useSidebar();

  // Create navigation items based on context
  const mainNavItems: NavItem[] = currentTeam
    ? [
        {
          title: 'Dashboard',
          href: team.dashboard({ team: currentTeam.slug }),
          icon: LayoutGrid,
        },
        // Only show Team Settings if user has view_settings permission
        ...(permissions.includes('view_settings')
          ? [
              {
                title: 'Team Settings',
                href: team.settings.general({ team: currentTeam.slug }),
                icon: Settings,
              },
            ]
          : []),
      ]
    : [
        {
          title: 'Dashboard',
          href: dashboard(),
          icon: LayoutGrid,
        },
      ];

  return (
    <Sidebar collapsible="icon" variant="inset">
      <SidebarHeader>
        <div className="px-2 py-2">
          <TeamSwitcher currentTeam={currentTeam} teams={teams} state={state} />
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
