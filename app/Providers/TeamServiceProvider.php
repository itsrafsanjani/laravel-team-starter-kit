<?php

namespace App\Providers;

use App\Services\TeamResolver;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class TeamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TeamResolver::class, function ($app) {
            return new TeamResolver($app->make(Request::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
