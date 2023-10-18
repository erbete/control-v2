<?php

use Illuminate\Support\Facades\Route;
use Control\Auth\Http\AuthController;

Route::prefix('auth')
    ->name('auth.')
    ->middleware(['auth:sanctum', 'blocked', 'userActivities'])
    ->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('user');
    });
