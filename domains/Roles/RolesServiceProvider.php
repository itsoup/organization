<?php

namespace Domains\Roles;

use Domains\Roles\Models\Role;
use Domains\Roles\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RolesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        $this->bootRoutes();

        $this->bootPolicies();
    }

    private function bootRoutes(): void
    {
        Route::middleware('api')
            ->group(fn () => $this->loadRoutesFrom(__DIR__ . '/Routes/api.php'));
    }

    private function bootPolicies(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
    }
}
