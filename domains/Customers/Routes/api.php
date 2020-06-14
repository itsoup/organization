<?php

use Domains\Customers\Http\Controllers\CustomersDeleteController;
use Domains\Customers\Http\Controllers\CustomersIndexController;
use Domains\Customers\Http\Controllers\CustomersShowController;
use Domains\Customers\Http\Controllers\CustomersStoreController;
use Domains\Customers\Http\Controllers\CustomersUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/customers', CustomersIndexController::class)->name('customers.index');
Route::get('/customers/{customerId}', CustomersShowController::class)->name('customers.show');
Route::post('/customers', CustomersStoreController::class)->name('customers.store');
Route::patch('/customers/{customerId}', CustomersUpdateController::class)->name('customers.update');
Route::delete('/customers/{customerId}', CustomersDeleteController::class)->name('customers.delete');
