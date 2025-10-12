<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use App\Traits\HasReservedUsernames;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateTeam
{
    use HasReservedUsernames;

    public function execute(User $user, array $data): Team
    {
        return DB::transaction(function () use ($user, $data) {
            $slug = $data['slug'] ?? $this->generateSlug($user->email);

            $team = Team::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'slug' => $slug,
                'type' => $data['type'] ?? 'personal',
                'website' => $data['website'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'] ?? null,
                'settings' => $data['settings'] ?? [],
            ]);

            // Add the user as the owner
            $team->users()->attach($user->id, [
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            return $team;
        });
    }

    private function generateSlug(string $email): string
    {
        $username = explode('@', $email)[0];
        $baseSlug = Str::slug($username);

        if (empty($baseSlug)) {
            $baseSlug = 'user';
        }

        return $this->generateUniqueSlug($baseSlug);
    }
}
