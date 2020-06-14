<?php

use Domains\Users\Http\Controllers\UsersDeleteAction;
use Domains\Users\Http\Controllers\UsersIndexAction;
use Domains\Users\Http\Controllers\UsersStoreAction;
use Domains\Users\Http\Controllers\UsersUpdateAction;
use Illuminate\Support\Facades\Route;

Route::get('/users', UsersIndexAction::class)->name('users.index');
Route::post('/users', UsersStoreAction::class)->name('users.store');
Route::patch('/users/{userId}', UsersUpdateAction::class)->name('users.update');
Route::delete('/users/{userId}', UsersDeleteAction::class)->name('users.delete');
