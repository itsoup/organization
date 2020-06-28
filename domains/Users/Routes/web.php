<?php

use Domains\Users\Http\Controllers\Verification\EmailVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)->name('verification.verify');
