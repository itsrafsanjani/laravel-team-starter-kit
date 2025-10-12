import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import teamInvitations from '@/routes/team-invitations';
import { type Invitation, type Team, type User } from '@/types';
import { Head } from '@inertiajs/react';
import { Building, User as UserIcon, Users } from 'lucide-react';

interface Props {
  invitation: Invitation;
  team: Team;
  user: User;
}

export default function InvitationAccept({ invitation, team, user }: Props) {
  return (
    <>
      <Head title={`Accept invitation to join ${team.name}`} />

      <div className="min-h-screen bg-gray-50 py-12 dark:bg-gray-900">
        <div className="mx-auto max-w-md px-4 sm:px-6 lg:px-8">
          <Card>
            <CardHeader className="text-center">
              <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                {team.type === 'company' ? (
                  <Building className="h-6 w-6 text-green-600" />
                ) : (
                  <UserIcon className="h-6 w-6 text-green-600" />
                )}
              </div>
              <CardTitle className="text-2xl">Accept Invitation</CardTitle>
              <CardDescription>
                You've been invited to join <strong>{team.name}</strong>
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="text-center">
                <p className="text-sm text-gray-600 dark:text-gray-400">
                  You'll join as a <strong>{invitation.role}</strong>
                </p>
                {team.description && (
                  <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {team.description}
                  </p>
                )}
              </div>

              <div className="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                <p className="text-sm text-green-800 dark:text-green-200">
                  You're logged in as <strong>{user.email}</strong>
                </p>
              </div>

              <div className="flex space-x-3">
                <form
                  method="post"
                  action={
                    teamInvitations.accept({ invitation: invitation.id }).url
                  }
                  className="flex-1"
                >
                  <Button type="submit" className="w-full">
                    <Users className="mr-2 h-4 w-4" />
                    Accept Invitation
                  </Button>
                </form>

                <form
                  method="post"
                  action={
                    teamInvitations.decline({ invitation: invitation.id }).url
                  }
                  className="flex-1"
                >
                  <input type="hidden" name="_method" value="DELETE" />
                  <Button type="submit" variant="outline" className="w-full">
                    Decline
                  </Button>
                </form>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}
