<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use App\Traits\HasReservedUsernames;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class UpdateTeam
{
    use HasReservedUsernames;

    public function execute(Team $team, User $user, array $data): Team
    {
        return DB::transaction(function () use ($team, $user, $data) {
            // Check if user can update this team
            if (! $this->canUpdateTeam($team, $user)) {
                throw new \InvalidArgumentException('You do not have permission to update this team.');
            }

            $updateData = [
                'name' => $data['name'] ?? $team->name,
                'type' => $data['type'] ?? $team->type,
                'description' => $data['description'] ?? $team->description,
                'website' => $data['website'] ?? $team->website,
                'phone' => $data['phone'] ?? $team->phone,
                'address' => $data['address'] ?? $team->address,
                'city' => $data['city'] ?? $team->city,
                'state' => $data['state'] ?? $team->state,
                'postal_code' => $data['postal_code'] ?? $team->postal_code,
                'country' => $data['country'] ?? $team->country,
            ];

            // Handle slug update with reserved username validation
            if (isset($data['slug']) && $data['slug'] !== $team->slug) {
                $updateData['slug'] = $this->generateUniqueSlug($data['slug'], $team->id);
            } else {
                $updateData['slug'] = $team->slug;
            }

            // Handle logo upload
            if (isset($data['logo']) && $data['logo']) {
                // Delete old logo if exists
                $oldLogo = $team->getRawOriginal('logo');
                if ($oldLogo) {
                    Storage::disk('public')->delete($oldLogo);
                }

                // Process and store new logo
                $logoPath = $this->processAndStoreLogo($data['logo'], $team->id);
                $updateData['logo'] = $logoPath;
            }

            $team->update($updateData);

            return $team->fresh();
        });
    }

    private function canUpdateTeam(Team $team, User $user): bool
    {
        return $user->ownsTeam($team) || in_array($user->teamRole($team), ['admin', 'owner']);
    }

    private function processAndStoreLogo($logoFile, string $teamId): string
    {
        $manager = new ImageManager(new Driver);

        // Create the image from the uploaded file
        $image = $manager->read($logoFile);

        // Since the image is already cropped on the frontend, we just need to ensure it's the right size
        // Resize to 128x128 pixels (retina: 128x128 for high DPI)
        $image->cover(128, 128, 'center');

        // Generate unique filename
        $filename = 'team-'.$teamId.'-'.time().'.jpg';
        $path = 'team-logos/'.$filename;

        // Store the processed image using Laravel's Storage facade
        $imageData = $image->toJpeg(95)->toString();
        Storage::disk('public')->put($path, $imageData);

        return $path;
    }
}
