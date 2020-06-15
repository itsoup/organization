<?php

use Domains\Roles\Http\Controllers\RolesIndexController;
use Domains\Roles\Http\Controllers\RolesStoreController;
use Illuminate\Support\Facades\Route;

Route::post('/roles', RolesStoreController::class)->name('roles.store');
Route::get('/roles', RolesIndexController::class)->name('roles.index');
