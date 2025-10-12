import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';
import { Head, Link } from '@inertiajs/react';
import { AlertTriangle, Home } from 'lucide-react';

export default function InvitationExpired() {
  return (
    <>
      <Head title="Invitation Expired" />

      <div className="min-h-screen bg-gray-50 py-12 dark:bg-gray-900">
        <div className="mx-auto max-w-md px-4 sm:px-6 lg:px-8">
          <Card>
            <CardHeader className="text-center">
              <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                <AlertTriangle className="h-6 w-6 text-red-600" />
              </div>
              <CardTitle className="text-2xl">Invitation Expired</CardTitle>
              <CardDescription>
                This invitation has expired and is no longer valid
              </CardDescription>
            </CardHeader>
            <CardContent className="text-center">
              <p className="mb-6 text-sm text-gray-600 dark:text-gray-400">
                Please contact the team owner to request a new invitation.
              </p>

              <Link href={home().url}>
                <Button>
                  <Home className="mr-2 h-4 w-4" />
                  Go Home
                </Button>
              </Link>
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}
