import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { dashboard } from '@/routes';
import team from '@/routes/team';
import teams from '@/routes/teams';
import { type Team } from '@/types';
import { Link, router } from '@inertiajs/react';
import { ChevronsUpDown, CircleCheck, Plus, Users } from 'lucide-react';
import { useState } from 'react';

interface Props {
  currentTeam?: Team;
  teams: Team[];
  state: 'collapsed' | 'expanded';
}

export default function TeamSwitcher({
  currentTeam,
  teams: userTeams,
  state,
}: Props) {
  const [switching, setSwitching] = useState<string | null>(null);

  const getTeamLogo = (team: Team) => {
    if (team.logo) {
      return (
        <div className="h-6 w-6 flex-shrink-0">
          <img
            src={team.logo}
            alt={`${team.name} logo`}
            className="h-full w-full rounded object-cover"
          />
        </div>
      );
    }

    return (
      <div className="flex h-6 w-6 items-center justify-center rounded bg-orange-500 text-xs font-bold text-white">
        {team.name.charAt(0).toUpperCase()}
      </div>
    );
  };

  const switchToTeam = (team: Team) => {
    setSwitching(team.id);
    router.visit(teams.switch.url({ team: team.slug }), {
      method: 'post',
      onFinish: () => setSwitching(null),
    });
  };

  if (!currentTeam) {
    return (
      <Link href={teams.index.url()}>
        <Button variant="outline" className="w-full justify-start">
          <Users className="mr-2 h-4 w-4" />
          Manage Teams
        </Button>
      </Link>
    );
  }

  if (state === 'collapsed') {
    return (
      <Link
        href={
          currentTeam ? team.dashboard({ team: currentTeam.slug }) : dashboard()
        }
        className="flex items-center justify-center"
      >
        {currentTeam?.logo ? (
          <div className="h-6 w-6 flex-shrink-0">
            <img
              src={currentTeam.logo}
              alt={`${currentTeam.name} logo`}
              className="h-full w-full rounded object-cover"
            />
          </div>
        ) : (
          <div className="h-6 w-6 flex-shrink-0">
            <div className="flex h-full w-full items-center justify-center rounded bg-orange-500 text-xs font-bold text-white">
              {currentTeam?.name?.charAt(0)?.toUpperCase() || 'T'}
            </div>
          </div>
        )}
      </Link>
    );
  }

  return (
    <div className="flex w-full items-center">
      <Link
        href={team.dashboard({ team: currentTeam.slug })}
        className="flex flex-1 items-center rounded-md px-2 py-1 transition-colors hover:bg-gray-200"
      >
        <div className="mr-2">
          <div className="h-6 w-6 flex-shrink-0">
            <img
              src={currentTeam.logo}
              alt={`${currentTeam.name} logo`}
              className="h-full w-full rounded object-cover"
            />
          </div>
        </div>
        <span className="truncate text-sm font-medium">{currentTeam.name}</span>
      </Link>
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <button className="cursor-pointer rounded-md p-1.5 transition-colors hover:bg-gray-100">
            <ChevronsUpDown className="h-4 w-4" />
          </button>
        </DropdownMenuTrigger>

        <DropdownMenuContent className="w-64" align="start">
          <div className="px-2 py-1.5">
            <p className="text-sm font-medium">Current Team</p>
            <p className="text-xs text-gray-500">{currentTeam.name}</p>
          </div>

          <DropdownMenuSeparator />

          {userTeams.map((team) => (
            <DropdownMenuItem
              key={team.id}
              onClick={() => team.id !== currentTeam.id && switchToTeam(team)}
              disabled={switching === team.id}
              className="flex items-center justify-between"
            >
              <div className="flex items-center">
                <div className="mr-2">
                  <div className="h-6 w-6 flex-shrink-0">
                    <img
                      src={team.logo}
                      alt={`${team.name} logo`}
                      className="h-full w-full rounded object-cover"
                    />
                  </div>
                </div>
                <span className="truncate">{team.name}</span>
              </div>
              {team.id === currentTeam.id && (
                <span className="text-green-600">
                  <CircleCheck className="inline h-4 w-4 text-green-600" />
                </span>
              )}
              {switching === team.id && (
                <span className="text-xs text-gray-500">Switching...</span>
              )}
            </DropdownMenuItem>
          ))}

          <DropdownMenuSeparator />

          <DropdownMenuItem asChild>
            <Link href={teams.create.url()} className="flex items-center">
              <Plus className="mr-2 h-4 w-4" />
              Create Team
            </Link>
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  );
}
