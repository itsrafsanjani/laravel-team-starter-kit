<?php

namespace App\Providers;

use App\Context\TeamContext;
use App\Models\Team;
use App\Services\RolePermissionService;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services as singletons for better performance
        $this->app->singleton(RolePermissionService::class);
        $this->app->singleton(TeamContext::class);

        // Register the facade binding
        $this->app->alias(TeamContext::class, 'team.context');

        // Cashier
        // Cashier::calculateTaxes();
        Cashier::useCustomerModel(Team::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
