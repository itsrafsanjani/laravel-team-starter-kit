import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import teamRoutes from '@/routes/team';
import { type Team } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { CheckCircle, Users } from 'lucide-react';

interface Props {
  team: Team;
}

export default function InvitationAlreadyMember({ team }: Props) {
  return (
    <>
      <Head title="Already a Member" />

      <div className="min-h-screen bg-gray-50 py-12 dark:bg-gray-900">
        <div className="mx-auto max-w-md px-4 sm:px-6 lg:px-8">
          <Card>
            <CardHeader className="text-center">
              <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                <CheckCircle className="h-6 w-6 text-green-600" />
              </div>
              <CardTitle className="text-2xl">Already a Member</CardTitle>
              <CardDescription>
                You are already a member of <strong>{team.name}</strong>
              </CardDescription>
            </CardHeader>
            <CardContent className="text-center">
              <p className="mb-6 text-sm text-gray-600 dark:text-gray-400">
                You are already a member of this team. You can access the team
                dashboard directly.
              </p>

              <Link href={teamRoutes.dashboard({ team: team.slug }).url}>
                <Button>
                  <Users className="mr-2 h-4 w-4" />
                  Go to Team Dashboard
                </Button>
              </Link>
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}
