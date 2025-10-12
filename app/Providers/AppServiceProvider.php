<?php

namespace App\Providers;

use App\Models\Team;
use App\Services\RolePermissionService;
use Illuminate\Database\Eloquent\Model;
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

        // Cashier
        // Cashier::calculateTaxes();
        Cashier::useCustomerModel(Team::class);

        Model::preventLazyLoading(! app()->environment('production'));
        Model::preventSilentlyDiscardingAttributes(! app()->environment('production'));

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
