<?php

use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('health-check', HealthCheckController::class)->name('health-check');
