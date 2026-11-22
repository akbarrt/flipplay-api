<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// CRUD standar Media (index, show, store, update, destroy)
// ->parameters() diperlukan karena Laravel meng-singularkan "media" menjadi "medium",
// sehingga {medium} tidak akan cocok dengan type-hint Media $media di controller.
Route::apiResource('media', MediaController::class)->parameters(['media' => 'media']);

// Pengguna (read-only; datanya dibuat lewat seeder)
Route::apiResource('users', UserController::class)->only(['index', 'show']);

// Custom action di luar CRUD: mengubah relasi many-to-many (koleksi pengguna)
Route::get('/users/{user}/collection', [UserController::class, 'collection']);
Route::post('/users/{user}/collection', [UserController::class, 'addToCollection']);
Route::delete('/users/{user}/collection/{media}', [UserController::class, 'removeFromCollection']);
