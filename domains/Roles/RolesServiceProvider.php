<?php

namespace Domains\Roles;

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
        $this->loadFactoriesFrom(__DIR__ . '/Database/Factories');

        $this->bootRoutes();
    }

    private function bootRoutes(): void
    {
        Route::middleware('api')
            ->group(fn () => $this->loadRoutesFrom(__DIR__ . '/Routes/api.php'));
    }
}
