import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { home, logout } from '@/routes';
import { type Invitation } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { AlertTriangle, LogOut } from 'lucide-react';

interface Props {
  invitation: Invitation;
  userEmail: string;
}

export default function InvitationWrongEmail({ invitation, userEmail }: Props) {
  return (
    <>
      <Head title="Wrong Email Address" />

      <div className="min-h-screen bg-gray-50 py-12 dark:bg-gray-900">
        <div className="mx-auto max-w-md px-4 sm:px-6 lg:px-8">
          <Card>
            <CardHeader className="text-center">
              <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                <AlertTriangle className="h-6 w-6 text-yellow-600" />
              </div>
              <CardTitle className="text-2xl">Wrong Email Address</CardTitle>
              <CardDescription>
                This invitation is not for your email address
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
                <p className="text-sm text-yellow-800 dark:text-yellow-200">
                  <strong>Invitation is for:</strong> {invitation.email}
                </p>
                <p className="text-sm text-yellow-800 dark:text-yellow-200">
                  <strong>You're logged in as:</strong> {userEmail}
                </p>
              </div>

              <p className="text-sm text-gray-600 dark:text-gray-400">
                Please log out and use the correct email address, or contact the
                team owner to send you a new invitation.
              </p>

              <div className="flex space-x-3">
                <Button
                  variant="outline"
                  className="flex-1"
                  onClick={() => router.post(logout().url)}
                >
                  <LogOut className="mr-2 h-4 w-4" />
                  Log Out
                </Button>

                <Link href={home().url} className="flex-1">
                  <Button className="w-full">Go Home</Button>
                </Link>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}
