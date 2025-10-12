import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { register } from '@/routes';
import { type Invitation, type Team } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { Building, Eye, EyeOff, User, Users } from 'lucide-react';
import { useState } from 'react';

interface Props {
  invitation: Invitation;
  team: Team;
}

export default function InvitationRegister({ invitation, team }: Props) {
  const [showPassword, setShowPassword] = useState(false);

  const { data, setData, post, processing, errors } = useForm({
    name: '',
    password: '',
    password_confirmation: '',
  });

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post(register().url);
  };

  return (
    <>
      <Head title={`Join ${team.name}`} />

      <div className="min-h-screen bg-gray-50 py-12 dark:bg-gray-900">
        <div className="mx-auto max-w-md px-4 sm:px-6 lg:px-8">
          <Card>
            <CardHeader className="text-center">
              <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                {team.type === 'company' ? (
                  <Building className="h-6 w-6 text-blue-600" />
                ) : (
                  <User className="h-6 w-6 text-blue-600" />
                )}
              </div>
              <CardTitle className="text-2xl">Join {team.name}</CardTitle>
              <CardDescription>
                Create your account to accept this invitation
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="mb-6 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                <p className="text-sm text-blue-800 dark:text-blue-200">
                  <strong>Email:</strong> {invitation.email}
                </p>
                <p className="text-sm text-blue-800 dark:text-blue-200">
                  <strong>Role:</strong> {invitation.role}
                </p>
              </div>

              <form onSubmit={submit} className="space-y-4">
                <div>
                  <Label htmlFor="name">Full Name</Label>
                  <Input
                    id="name"
                    type="text"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    className="mt-1"
                    required
                  />
                  {errors.name && (
                    <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                  )}
                </div>

                <div>
                  <Label htmlFor="email">Email</Label>
                  <Input
                    id="email"
                    type="email"
                    value={invitation.email}
                    disabled
                    className="mt-1 bg-gray-100 dark:bg-gray-800"
                  />
                  <p className="mt-1 text-xs text-gray-500">
                    This email is pre-filled from your invitation
                  </p>
                </div>

                <div>
                  <Label htmlFor="password">Password</Label>
                  <div className="relative">
                    <Input
                      id="password"
                      type={showPassword ? 'text' : 'password'}
                      value={data.password}
                      onChange={(e) => setData('password', e.target.value)}
                      className="mt-1 pr-10"
                      required
                    />
                    <button
                      type="button"
                      className="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                      onClick={() => setShowPassword(!showPassword)}
                    >
                      {showPassword ? (
                        <EyeOff className="h-4 w-4" />
                      ) : (
                        <Eye className="h-4 w-4" />
                      )}
                    </button>
                  </div>
                  {errors.password && (
                    <p className="mt-1 text-sm text-red-600">
                      {errors.password}
                    </p>
                  )}
                </div>

                <div>
                  <Label htmlFor="password_confirmation">
                    Confirm Password
                  </Label>
                  <Input
                    id="password_confirmation"
                    type="password"
                    value={data.password_confirmation}
                    onChange={(e) =>
                      setData('password_confirmation', e.target.value)
                    }
                    className="mt-1"
                    required
                  />
                  {errors.password_confirmation && (
                    <p className="mt-1 text-sm text-red-600">
                      {errors.password_confirmation}
                    </p>
                  )}
                </div>

                {Object.keys(errors).length > 0 && (
                  <div className="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                    <p className="text-sm text-red-800 dark:text-red-200">
                      Please fix the errors above to continue.
                    </p>
                  </div>
                )}

                <Button type="submit" className="w-full" disabled={processing}>
                  <Users className="mr-2 h-4 w-4" />
                  {processing
                    ? 'Creating Account...'
                    : 'Create Account & Join Team'}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}
