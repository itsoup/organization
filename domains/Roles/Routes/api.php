<?php

use Domains\Roles\Http\Controllers\RolesDeleteController;
use Domains\Roles\Http\Controllers\RolesIndexController;
use Domains\Roles\Http\Controllers\RolesStoreController;
use Domains\Roles\Http\Controllers\RolesUpdateController;
use Illuminate\Support\Facades\Route;

Route::post('/roles', RolesStoreController::class)->name('roles.store');
Route::get('/roles', RolesIndexController::class)->name('roles.index');
Route::patch('/roles/{roleId}', RolesUpdateController::class)->name('roles.update');
Route::delete('/roles/{roleId}', RolesDeleteController::class)->name('roles.delete');
