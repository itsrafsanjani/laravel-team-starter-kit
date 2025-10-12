<?php

namespace App\Models;

use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    public function isLifetime(): bool
    {
        return ! $this->onTrial() && $this->ends_at === null;
    }
}
