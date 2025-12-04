<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define Gates for role-based authorization
        Gate::define('view-users', function ($user) {
            return $user->role && $user->role->name === 'Admin';
        });

        Gate::define('is-admin', function ($user) {
            return $user->role && $user->role->name === 'Admin';
        });
    }
}
