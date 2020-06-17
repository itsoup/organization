<?php

use Domains\Users\Http\Controllers\UsersDeleteController;
use Domains\Users\Http\Controllers\UsersIndexController;
use Domains\Users\Http\Controllers\Roles\RolesUsersStoreController;
use Domains\Users\Http\Controllers\UsersShowController;
use Domains\Users\Http\Controllers\UsersStoreController;
use Domains\Users\Http\Controllers\UsersUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/users', UsersIndexController::class)->name('users.index');
Route::post('/users', UsersStoreController::class)->name('users.store');
Route::get('/users/{userId}', UsersShowController::class)->name('users.show');
Route::patch('/users/{userId}', UsersUpdateController::class)->name('users.update');
Route::delete('/users/{userId}', UsersDeleteController::class)->name('users.delete');

Route::put('/users/{userId}/roles', RolesUsersStoreController::class)->name('users_roles.store');
