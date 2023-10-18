<?php

use Control\Rebinding\Http\RebindingController;
use Illuminate\Support\Facades\Route;

Route::prefix('rebinding')->name('rebinding.')->group(function () {
    Route::group(['middleware' => ['auth:sanctum', 'blocked', 'permission:rebinding', 'userActivities']], function () {
        Route::get('/', [RebindingController::class, 'index'])->name('index');
        Route::get('/details/{accountId}', [RebindingController::class, 'details'])->name('details');
        Route::patch('/details/{accountId}/set-note', [RebindingController::class, 'setNote'])->name('details.setNote');
        Route::patch('/details/{accountId}/set-status', [RebindingController::class, 'setStatus'])->name('details.setStatus');
        Route::get('/rebinded-accounts', [RebindingController::class, 'rebindedAccounts'])->name('rebindedAccounts');
    });
});
