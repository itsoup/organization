<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map(): void
    {
        Route::middleware('api')
            ->group(base_path('routes/api.php'));
    }
}
