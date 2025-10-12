<?php

namespace App\Enums;

enum PlanType: string
{
    case FREE = 'free';
    case TRIAL = 'trial';
    case SUBSCRIPTION = 'subscription';
    case LIFETIME = 'lifetime';

    /**
     * Get all plan type values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get a human-readable label for the plan type.
     */
    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Free',
            self::TRIAL => 'Trial',
            self::SUBSCRIPTION => 'Subscription',
            self::LIFETIME => 'Lifetime',
        };
    }

    /**
     * Check if the plan type is a paid plan.
     */
    public function isPaid(): bool
    {
        return match ($this) {
            self::FREE, self::TRIAL => false,
            self::SUBSCRIPTION, self::LIFETIME => true,
        };
    }

    /**
     * Check if the plan type has a trial period.
     */
    public function hasTrial(): bool
    {
        return $this === self::TRIAL;
    }
}
