<?php

namespace Domains\Customers;

use Domains\Customers\Models\Customer;
use Domains\Customers\Policies\CustomerPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CustomersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadFactoriesFrom(__DIR__ . '/Database/Factories');

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
        Gate::policy(Customer::class, CustomerPolicy::class);
    }
}
