<?php

namespace Domains\Users;

use Domains\Users\Console\Commands\UsersCreateCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadFactoriesFrom(__DIR__ . '/Database/Factories');

        $this->bootCommands();

        $this->bootRoutes();
    }

    private function bootCommands(): void
    {
        $this->commands([
            UsersCreateCommand::class,
        ]);
    }

    private function bootRoutes(): void
    {
        Route::middleware('api')
            ->group(fn () => $this->loadRoutesFrom(__DIR__ . '/Routes/api.php'));
    }
}
