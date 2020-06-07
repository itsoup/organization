<?php

use App\Http\Controllers\Customers\CustomersDeleteAction;
use App\Http\Controllers\Customers\CustomersIndexAction;
use App\Http\Controllers\Customers\CustomersShowAction;
use App\Http\Controllers\Customers\CustomersStoreAction;
use App\Http\Controllers\Customers\CustomersUpdateAction;
use App\Http\Controllers\Users\UsersDeleteAction;
use App\Http\Controllers\Users\UsersIndexAction;
use App\Http\Controllers\Users\UsersLoginAction;
use App\Http\Controllers\Users\UsersStoreAction;
use App\Http\Controllers\Users\UsersUpdateAction;
use Illuminate\Support\Facades\Route;

Route::post('/login', UsersLoginAction::class)->name('login');

Route::prefix('/organization')
    ->group(static function () {
        Route::get('/users', UsersIndexAction::class)->name('users.index');
        Route::post('/users', UsersStoreAction::class)->name('users.store');
        Route::patch('/users/{userId}', UsersUpdateAction::class)->name('users.update');
        Route::delete('/users/{userId}', UsersDeleteAction::class)->name('users.delete');

        Route::get('/customers', CustomersIndexAction::class)->name('customers.index');
        Route::get('/customers/{customerId}', CustomersShowAction::class)->name('customers.show');
        Route::post('/customers', CustomersStoreAction::class)->name('customers.store');
        Route::patch('/customers/{customerId}', CustomersUpdateAction::class)->name('customers.update');
        Route::delete('/customers/{customerId}', CustomersDeleteAction::class)->name('customers.delete');
    });
