<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'avatar',
        'is_banned',
        'banned_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
        ];
    }

    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function teamInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'email', 'email');
    }

    public function belongsToTeam(Team $team): bool
    {
        return $this->ownsTeam($team) || $this->teams()->where('team_id', $team->id)->exists();
    }

    public function ownsTeam(Team $team): bool
    {
        return $this->id === $team->user_id;
    }

    public function teamRole(Team $team): ?string
    {
        if ($this->ownsTeam($team)) {
            return 'owner';
        }

        $membership = $this->teams()->where('team_id', $team->id)->first();

        return $membership?->pivot?->role;
    }

    public function getDefaultTeam(): ?Team
    {
        // First try to get a personal team owned by the user
        $personalTeam = $this->ownedTeams()->where('type', 'personal')->first();

        if ($personalTeam) {
            return $personalTeam;
        }

        // If no personal team, get the first team they belong to
        return $this->teams()->first();
    }

    public function hasAnyTeam(): bool
    {
        return $this->teams()->exists() || $this->ownedTeams()->exists();
    }

    public function adminRole(): BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'user_admin_roles')
            ->withPivot(['is_active', 'assigned_at', 'expires_at'])
            ->withTimestamps()
            ->wherePivot('is_active', true)
            ->where(function ($query) {
                $query->whereNull('user_admin_roles.expires_at')
                    ->orWhere('user_admin_roles.expires_at', '>', now());
            });
    }

    public function canAccessAdminPanel(): bool
    {
        return $this->adminRole()->exists();
    }

    public function hasAdminRole(string $roleSlug): bool
    {
        return $this->adminRole()
            ->where('slug', $roleSlug)
            ->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->adminRole()
            ->where('admin_roles.is_active', true)
            ->whereJsonContains('permissions', $permission)
            ->exists();
    }

    public function isBanned(): bool
    {
        return (bool) $this->is_banned;
    }

    public function ban(?string $reason = null): void
    {
        $this->update([
            'is_banned' => true,
            'banned_reason' => $reason,
        ]);
    }

    public function unban(): void
    {
        $this->update([
            'is_banned' => false,
            'banned_reason' => null,
        ]);
    }

    public function getAvatarAttribute(): string
    {
        if (isset($this->attributes['avatar']) && $this->attributes['avatar']) {
            return Storage::disk('public')->url($this->attributes['avatar']);
        }

        return 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($this->email))).'?d=identicon&s=200';
    }
}
