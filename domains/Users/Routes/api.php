<?php

use Domains\Users\Http\Controllers\Me\MeShowController;
use Domains\Users\Http\Controllers\Me\MeTokensDeleteController;
use Domains\Users\Http\Controllers\Me\MeTokensIndexController;
use Domains\Users\Http\Controllers\Me\MeUpdateController;
use Domains\Users\Http\Controllers\Roles\RolesUsersIndexController;
use Domains\Users\Http\Controllers\Roles\RolesUsersStoreController;
use Domains\Users\Http\Controllers\Users\UsersDeleteController;
use Domains\Users\Http\Controllers\Users\UsersIndexController;
use Domains\Users\Http\Controllers\Users\UsersShowController;
use Domains\Users\Http\Controllers\Users\UsersStoreController;
use Domains\Users\Http\Controllers\Users\UsersUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/users', UsersIndexController::class)->name('users.index');
Route::post('/users', UsersStoreController::class)->name('users.store');
Route::get('/users/{userId}', UsersShowController::class)->name('users.show');
Route::patch('/users/{userId}', UsersUpdateController::class)->name('users.update');
Route::delete('/users/{userId}', UsersDeleteController::class)->name('users.delete');

Route::get('/users/{userId}/roles', RolesUsersIndexController::class)->name('users_roles.index');
Route::put('/users/{userId}/roles', RolesUsersStoreController::class)->name('users_roles.store');

Route::get('/me', MeShowController::class)->name('me.show');
Route::patch('/me', MeUpdateController::class)->name('me.update');

Route::get('/me/tokens', MeTokensIndexController::class)->name('me_tokens.index');
Route::delete('/me/tokens/{id}', MeTokensDeleteController::class)->name('me_tokens.delete');
