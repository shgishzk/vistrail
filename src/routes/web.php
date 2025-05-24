<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/pins', [PinController::class, 'index']);
//     Route::post('/pins', [PinController::class, 'store']);
//     Route::put('/pins/{id}', [PinController::class, 'update']);
//     Route::delete('/pins/{id}', [PinController::class, 'destroy']);
// });

Route::view('/admin', 'admin.dashboard');