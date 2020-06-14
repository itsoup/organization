<?php

namespace Domains\Users;

use Domains\Users\Console\Commands\UsersCreateCommand;
use Domains\Users\Repositories\AccessTokenRepository;
use Domains\Users\Repositories\UserRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;
use Laravel\Passport\Bridge\UserRepository as PassportUserRepository;

class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PassportAccessTokenRepository::class, AccessTokenRepository::class);
        $this->app->bind(PassportUserRepository::class, UserRepository::class);
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
