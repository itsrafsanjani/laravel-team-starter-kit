import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import { login } from '@/routes';
import { type TeamInvitation } from '@/types';
import { Form, Head, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

interface PageProps {
  invitation?: TeamInvitation;
  prefilledEmail?: string;
  [key: string]: any;
}

export default function Register() {
  const { invitation, prefilledEmail } = usePage<PageProps>().props;
  const isInvited = !!invitation;
  const emailValue = prefilledEmail || '';

  return (
    <AuthLayout
      title={isInvited ? 'Join the team' : 'Create an account'}
      description={
        isInvited
          ? `You've been invited to join ${invitation?.team?.name || 'the team'} as a ${invitation?.role || 'member'}. Create your account to get started.`
          : 'Enter your details below to create your account'
      }
    >
      <Head title="Register" />
      <Form
        {...RegisteredUserController.store.form()}
        resetOnSuccess={['password', 'password_confirmation']}
        disableWhileProcessing
        className="flex flex-col gap-6"
      >
        {({ processing, errors }) => (
          <>
            {/* Hidden fields for invitation data */}
            {invitation?.id && (
              <input type="hidden" name="invitation_id" value={invitation.id} />
            )}
            {isInvited && (
              <input type="hidden" name="email" value={emailValue} />
            )}
            <div className="grid gap-6">
              <div className="grid gap-2">
                <Label htmlFor="name">Name</Label>
                <Input
                  id="name"
                  type="text"
                  required
                  autoFocus
                  tabIndex={1}
                  autoComplete="name"
                  name="name"
                  placeholder="Full name"
                />
                <InputError message={errors.name} className="mt-2" />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="email">Email address</Label>
                <Input
                  id="email"
                  type="email"
                  required
                  tabIndex={2}
                  autoComplete="email"
                  name={isInvited ? undefined : 'email'}
                  placeholder="email@example.com"
                  {...(isInvited ? { value: emailValue, readOnly: true } : {})}
                  disabled={isInvited}
                  className={isInvited ? 'bg-muted' : ''}
                />
                {isInvited && (
                  <p className="text-sm text-muted-foreground">
                    This email is pre-filled from your invitation
                  </p>
                )}
                <InputError message={errors.email} />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="password">Password</Label>
                <Input
                  id="password"
                  type="password"
                  required
                  tabIndex={3}
                  autoComplete="new-password"
                  name="password"
                  placeholder="Password"
                />
                <InputError message={errors.password} />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="password_confirmation">Confirm password</Label>
                <Input
                  id="password_confirmation"
                  type="password"
                  required
                  tabIndex={4}
                  autoComplete="new-password"
                  name="password_confirmation"
                  placeholder="Confirm password"
                />
                <InputError message={errors.password_confirmation} />
              </div>

              <Button
                type="submit"
                className="mt-2 w-full"
                tabIndex={5}
                data-test="register-user-button"
              >
                {processing && (
                  <LoaderCircle className="h-4 w-4 animate-spin" />
                )}
                {isInvited ? 'Join the team' : 'Create account'}
              </Button>
            </div>

            <div className="text-center text-sm text-muted-foreground">
              {isInvited
                ? 'Already have an account? '
                : 'Already have an account? '}
              <TextLink href={login()} tabIndex={6}>
                Log in
              </TextLink>
            </div>
          </>
        )}
      </Form>
    </AuthLayout>
  );
}
