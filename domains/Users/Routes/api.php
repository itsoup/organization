<?php

use Domains\Users\Http\Controllers\UsersDeleteController;
use Domains\Users\Http\Controllers\UsersIndexController;
use Domains\Users\Http\Controllers\UsersStoreController;
use Domains\Users\Http\Controllers\UsersUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/users', UsersIndexController::class)->name('users.index');
Route::post('/users', UsersStoreController::class)->name('users.store');
Route::patch('/users/{userId}', UsersUpdateController::class)->name('users.update');
Route::delete('/users/{userId}', UsersDeleteController::class)->name('users.delete');
