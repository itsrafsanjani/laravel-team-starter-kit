<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;

class Team extends Model
{
    use Billable;
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'type',
        'description',
        'billing_email',
        'billing_name',
        'tax_id',
        'logo',
        'website',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Team $team) {
            if (empty($team->slug)) {
                $team->slug = Str::slug($team->name);
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function members(): BelongsToMany
    {
        return $this->users()->wherePivot('role', '!=', 'owner');
    }

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function userRole(User $user): ?string
    {
        $membership = $this->users()->where('user_id', $user->id)->first();

        return $membership?->pivot?->role;
    }

    public function isPersonal(): bool
    {
        return $this->type === 'personal';
    }

    public function isCompany(): bool
    {
        return $this->type === 'company';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getLogoAttribute($value): string
    {
        if ($value) {
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return $value;
            }

            return asset('storage/'.$value);
        }

        // Generate a fallback avatar URL using UI Avatars when no logo is set
        $name = urlencode($this->attributes['name']);
        $background = 'f97316'; // Orange color
        $color = 'ffffff'; // White text

        return "https://ui-avatars.com/api/?name={$name}&background={$background}&color={$color}&size=24&bold=true";
    }

    /**
     * Get the active plan for the team
     *
     * @param  string  $subscription
     * @return array{plan: Plan|null, cycle: string|null, subscription: \Laravel\Cashier\Subscription|null}
     */
    public function getActivePlan($subscription = 'default'): array
    {
        $subscription = $this->subscriptions()
            ->where('type', $subscription)
            ->where('stripe_status', 'active')
            ->first();

        if (! $subscription) {
            return [
                'plan' => null,
                'cycle' => null,
                'subscription' => null,
            ];
        }

        $plan = Plan::query()
            ->where('stripe_monthly_price_id', $subscription->stripe_price)
            ->orWhere('stripe_yearly_price_id', $subscription->stripe_price)
            ->orWhere('stripe_lifetime_price_id', $subscription->stripe_price)
            ->first();

        if (! $plan) {
            return [
                'plan' => null,
                'cycle' => null,
                'subscription' => $subscription,
            ];
        }

        $cycle = $plan->getCycleForPriceId($subscription->stripe_price);

        return [
            'plan' => $plan,
            'cycle' => $cycle,
            'subscription' => $subscription,
        ];
    }

    /**
     * Get the subscriptions for the team.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
