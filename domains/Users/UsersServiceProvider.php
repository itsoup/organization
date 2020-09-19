<?php

namespace Domains\Users;

use Domains\Users\Bridges\AccessTokenRepository;
use Domains\Users\Console\Commands\UsersCreateCommand;
use Domains\Users\Models\User;
use Domains\Users\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;

class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PassportAccessTokenRepository::class, AccessTokenRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        $this->bootCommands();

        $this->bootRoutes();

        $this->bootPolicies();
    }

    private function bootCommands(): void
    {
        $this->commands([
            UsersCreateCommand::class,
        ]);
    }

    private function bootRoutes(): void
    {
        Route::middleware('web')
            ->group(fn () => $this->loadRoutesFrom(__DIR__ . '/Routes/web.php'));

        Route::middleware('api')
            ->group(fn () => $this->loadRoutesFrom(__DIR__ . '/Routes/api.php'));
    }

    private function bootPolicies(): void
    {
        Gate::policy(User::class, UserPolicy::class);
    }
}
