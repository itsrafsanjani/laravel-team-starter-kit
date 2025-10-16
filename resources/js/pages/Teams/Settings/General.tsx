import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import ImageCropper from '@/components/ui/image-cropper';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import TeamSettingsLayout from '@/layouts/teams/layout';
import teamRoutes from '@/routes/team';
import { type BreadcrumbItem, type Team } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';

interface Props {
  team: Team;
  userRole: string;
  canDelete: boolean;
  isOnlyTeam: boolean;
}

export default function TeamGeneralSettings({
  team,
  canDelete,
  isOnlyTeam,
}: Props) {
  const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
  const [showSlugField, setShowSlugField] = useState(false);
  const [isCropperOpen, setIsCropperOpen] = useState(false);
  const [selectedImageFile, setSelectedImageFile] = useState<File | null>(null);

  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Teams',
      href: teamRoutes.dashboard({ team: team.slug }).url,
    },
    {
      title: 'Team Settings',
      href: '',
    },
  ];

  const { data, setData, post, processing, errors } = useForm({
    name: team.name,
    slug: team.slug,
    logo: null as File | null,
  });

  const deleteForm = useForm({
    password: '',
  });

  const submit = (e: React.FormEvent) => {
    e.preventDefault();

    // Use post method with forceFormData for file uploads
    post(teamRoutes.settings.general.update({ team: team.slug }).url, {
      forceFormData: true,
      onSuccess: () => {
        // Reset form state after successful save
        setData('logo', null);
        setShowSlugField(false);
      },
    });
  };

  const handleDelete = () => {
    deleteForm.post(teamRoutes.delete({ team: team.slug }).url, {
      onSuccess: () => {
        router.visit(
          teamRoutes.settings.general.index({ team: team.slug }).url,
        );
      },
    });
  };

  const handleImageSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setSelectedImageFile(file);
      setIsCropperOpen(true);
    }
  };

  const handleCropComplete = (croppedImageBlob: Blob) => {
    // Convert blob to File object
    const croppedFile = new File([croppedImageBlob], 'cropped-logo.jpg', {
      type: 'image/jpeg',
    });
    setData('logo', croppedFile);
    setSelectedImageFile(null);
  };

  const handleCropperClose = () => {
    setIsCropperOpen(false);
    setSelectedImageFile(null);
    // Reset the file input
    const fileInput = document.getElementById('logo') as HTMLInputElement;
    if (fileInput) {
      fileInput.value = '';
    }
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${team.name} - General Settings`} />

      <TeamSettingsLayout team={team}>
        <div className="space-y-6">
          <div>
            <h3 className="text-lg font-medium">General</h3>
            <p className="text-sm text-gray-600 dark:text-gray-400">
              General settings related to this team.
            </p>
          </div>

          <form onSubmit={submit} className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Team Information</CardTitle>
                <CardDescription>
                  Update your team name and avatar.
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                <div>
                  <Label htmlFor="name">Team name</Label>
                  <Input
                    id="name"
                    type="text"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    placeholder="Enter team name"
                    className="mt-1"
                  />
                  {errors.name && (
                    <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                  )}
                  <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Your handle is {data.slug}.{' '}
                    <button
                      type="button"
                      onClick={() => setShowSlugField(!showSlugField)}
                      className="font-semibold text-gray-800 hover:underline dark:text-gray-200"
                    >
                      Change
                    </button>
                  </p>
                </div>

                {showSlugField && (
                  <div>
                    <Label htmlFor="slug">Team handle</Label>
                    <Input
                      id="slug"
                      type="text"
                      value={data.slug}
                      onChange={(e) => setData('slug', e.target.value)}
                      placeholder="Enter team handle"
                      className="mt-1"
                    />
                    {errors.slug && (
                      <p className="mt-1 text-sm text-red-600">{errors.slug}</p>
                    )}
                    <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                      This will be your team's unique URL handle. Only lowercase
                      letters, numbers, and hyphens are allowed.
                    </p>
                  </div>
                )}

                <div>
                  <Label>Team avatar</Label>
                  <div className="mt-1 flex items-center space-x-4">
                    <div className="flex-shrink-0">
                      {data.logo ? (
                        <img
                          src={URL.createObjectURL(data.logo)}
                          alt="Selected logo preview"
                          className="h-16 w-16 rounded-lg object-cover"
                        />
                      ) : team.logo ? (
                        <img
                          src={team.logo}
                          alt="Current logo"
                          className="h-16 w-16 rounded-lg object-cover"
                        />
                      ) : (
                        <div className="flex h-16 w-16 items-center justify-center rounded-lg bg-orange-500 text-2xl font-bold text-white">
                          {team.name.charAt(0).toUpperCase()}
                        </div>
                      )}
                    </div>
                    <div className="flex-1">
                      <div className="flex items-center space-x-2">
                        <Button
                          type="button"
                          variant="outline"
                          onClick={() =>
                            document.getElementById('logo')?.click()
                          }
                        >
                          {data.logo ? 'Change file' : 'Upload file'}
                        </Button>
                        {data.logo && (
                          <Button
                            type="button"
                            variant="outline"
                            onClick={() => setData('logo', null)}
                            className="text-red-600 hover:text-red-700"
                          >
                            Remove
                          </Button>
                        )}
                      </div>
                      <Input
                        id="logo"
                        type="file"
                        accept="image/*"
                        onChange={handleImageSelect}
                        className="hidden"
                      />
                      <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Upload a logo for your team (max 2MB, JPG, PNG, GIF,
                        SVG)
                      </p>
                      {errors.logo && (
                        <p className="mt-1 text-sm text-red-600">
                          {errors.logo}
                        </p>
                      )}
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <div className="flex justify-end">
              <Button type="submit" disabled={processing}>
                {processing ? 'Saving...' : 'Save Changes'}
              </Button>
            </div>
          </form>

          {/* Danger Section */}
          <div className="space-y-6">
            <div>
              <h3 className="text-lg font-medium">Danger</h3>
              <p className="text-sm text-gray-600 dark:text-gray-400">
                Destructive settings that cannot be undone.
              </p>
            </div>

            <Card>
              <CardHeader>
                <CardTitle>Delete team</CardTitle>
                <CardDescription>
                  Deleting your team will permanently delete all of its
                  applications, environments, and resources.
                </CardDescription>
              </CardHeader>
              <CardContent>
                {isOnlyTeam ? (
                  <div className="rounded-md bg-yellow-50 p-4">
                    <p className="text-sm text-yellow-800">
                      You cannot delete your only team. Create another
                      organization first.
                    </p>
                  </div>
                ) : !canDelete ? (
                  <div className="rounded-md bg-red-50 p-4">
                    <p className="text-sm text-red-800">
                      You do not have permission to delete this team.
                    </p>
                  </div>
                ) : (
                  <AlertDialog
                    open={isDeleteDialogOpen}
                    onOpenChange={setIsDeleteDialogOpen}
                  >
                    <AlertDialogTrigger asChild>
                      <Button
                        variant="destructive"
                        className="flex items-center space-x-2"
                      >
                        <Trash2 className="h-4 w-4" />
                        <span>Delete team</span>
                      </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                      <AlertDialogHeader>
                        <AlertDialogTitle>
                          Are you absolutely sure?
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                          This action cannot be undone. This will permanently
                          delete the organization and remove all data from our
                          servers.
                        </AlertDialogDescription>
                      </AlertDialogHeader>
                      <div className="space-y-4">
                        <div>
                          <Label htmlFor="delete-password">Password</Label>
                          <Input
                            id="delete-password"
                            type="password"
                            value={deleteForm.data.password}
                            onChange={(e) =>
                              deleteForm.setData('password', e.target.value)
                            }
                            placeholder="Enter your password to confirm"
                          />
                          {deleteForm.errors.password && (
                            <p className="mt-1 text-sm text-red-600">
                              {deleteForm.errors.password}
                            </p>
                          )}
                        </div>
                      </div>
                      <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                          onClick={handleDelete}
                          disabled={
                            deleteForm.processing || !deleteForm.data.password
                          }
                          className="bg-red-600 hover:bg-red-700"
                        >
                          {deleteForm.processing
                            ? 'Deleting...'
                            : 'Delete organization'}
                        </AlertDialogAction>
                      </AlertDialogFooter>
                    </AlertDialogContent>
                  </AlertDialog>
                )}
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Image Cropper Modal */}
        <ImageCropper
          isOpen={isCropperOpen}
          onClose={handleCropperClose}
          onCrop={handleCropComplete}
          imageFile={selectedImageFile}
          aspectRatio={1}
          minWidth={100}
          minHeight={100}
        />
      </TeamSettingsLayout>
    </AppLayout>
  );
}
