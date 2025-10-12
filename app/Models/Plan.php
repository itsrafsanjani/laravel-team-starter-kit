<?php

namespace App\Models;

use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'monthly_price',
        'yearly_price',
        'lifetime_price',
        'stripe_monthly_price_id',
        'stripe_yearly_price_id',
        'stripe_lifetime_price_id',
        'trial_days',
        'features',
        'permissions',
        'is_active',
        'is_popular',
        'is_legacy',
        'sort_order',
    ];

    protected $casts = [
        'type' => PlanType::class,
        'features' => 'array',
        'permissions' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'is_legacy' => 'boolean',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'lifetime_price' => 'decimal:2',
    ];

    public function isFree(): bool
    {
        return $this->type === PlanType::FREE;
    }

    public function isTrial(): bool
    {
        return $this->type === PlanType::TRIAL;
    }

    public function isSubscription(): bool
    {
        return $this->type === PlanType::SUBSCRIPTION;
    }

    public function isLifetime(): bool
    {
        return $this->type === PlanType::LIFETIME;
    }

    public function hasTrial(): bool
    {
        return $this->trial_days > 0;
    }

    public function getPriceForCycle(string $cycle): ?float
    {
        return match ($cycle) {
            'monthly' => $this->monthly_price,
            'yearly' => $this->yearly_price,
            'lifetime' => $this->lifetime_price,
            default => null,
        };
    }

    public function getStripePriceIdForCycle(string $cycle): ?string
    {
        return match ($cycle) {
            'monthly' => $this->stripe_monthly_price_id,
            'yearly' => $this->stripe_yearly_price_id,
            'lifetime' => $this->stripe_lifetime_price_id,
            default => null,
        };
    }

    public function getCycleForPriceId(string $priceId): ?string
    {
        return match ($priceId) {
            $this->stripe_monthly_price_id => 'monthly',
            $this->stripe_yearly_price_id => 'yearly',
            $this->stripe_lifetime_price_id => 'lifetime',
            default => null,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, PlanType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        return in_array($permission, $permissions);
    }

    public function getPermissionValue(string $permission, $default = null)
    {
        $permissions = $this->permissions ?? [];

        return $permissions[$permission] ?? $default;
    }

    public function isLegacy(): bool
    {
        return $this->is_legacy;
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_legacy', false)->where('is_active', true);
    }

    public function scopeLegacy($query)
    {
        return $query->where('is_legacy', true);
    }

    public function scopeAvailableForNewCustomers($query)
    {
        return $query->where('is_legacy', false)->where('is_active', true);
    }
}
