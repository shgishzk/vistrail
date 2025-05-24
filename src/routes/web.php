<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;

Route::view('/', 'home')->name('home');

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/pins', [PinController::class, 'index']);
//     Route::post('/pins', [PinController::class, 'store']);
//     Route::put('/pins/{id}', [PinController::class, 'update']);
//     Route::delete('/pins/{id}', [PinController::class, 'destroy']);
// });

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::middleware('admin.auth')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('users', [\App\Http\Controllers\Admin\UsersController::class, 'index'])->name('users');
    });
});
