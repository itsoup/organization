<?php

use Domains\Customers\Http\Controllers\CustomersDeleteAction;
use Domains\Customers\Http\Controllers\CustomersIndexAction;
use Domains\Customers\Http\Controllers\CustomersShowAction;
use Domains\Customers\Http\Controllers\CustomersStoreAction;
use Domains\Customers\Http\Controllers\CustomersUpdateAction;
use Illuminate\Support\Facades\Route;

Route::get('/customers', CustomersIndexAction::class)->name('customers.index');
Route::get('/customers/{customerId}', CustomersShowAction::class)->name('customers.show');
Route::post('/customers', CustomersStoreAction::class)->name('customers.store');
Route::patch('/customers/{customerId}', CustomersUpdateAction::class)->name('customers.update');
Route::delete('/customers/{customerId}', CustomersDeleteAction::class)->name('customers.delete');
