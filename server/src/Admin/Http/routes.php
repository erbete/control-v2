<?php

use Illuminate\Support\Facades\Route;
use Control\Admin\Http\AdminController;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:sanctum', 'blocked', 'userActivities', 'permission:admin'])
    ->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{id}', [AdminController::class, 'user'])->name('user');
        Route::post('/users/register', [AdminController::class, 'registerUser'])->name('registerUser');
        Route::put('/users/{id}/edit', [AdminController::class, 'editUser'])->name('editUser');

        Route::get('/permissions', [AdminController::class, 'permissions'])->name('permissions');
        Route::get('/permissions/{id}', [AdminController::class, 'permission'])->name('permission');
        Route::post('/permissions/create', [AdminController::class, 'createPermission'])->name('createPermission');
        Route::put('/permissions/{id}/edit', [AdminController::class, 'editPermission'])->name('editPermission');
        Route::delete('/permissions/{id}/delete', [AdminController::class, 'deletePermission'])->name('deletePermission');
        Route::post('/permissions/detach', [AdminController::class, 'detachUserFromPermission'])->name('detachUserFromPermission');
    });
