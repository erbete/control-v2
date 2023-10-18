<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

// Route that will be executed when no other route matches the incoming request
Route::fallback(fn () => abort(Response::HTTP_NOT_FOUND));

Route::get('/ping', fn () => response()->json(['message' => 'pong']));
Route::get('/auth-ping', fn () => response()->json(['message' => 'pong']))->middleware(['auth:sanctum', 'blocked']);
