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
        Route::get('users/create', [\App\Http\Controllers\Admin\UsersController::class, 'create'])->name('users.create');
        Route::post('users', [\App\Http\Controllers\Admin\UsersController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [\App\Http\Controllers\Admin\UsersController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [\App\Http\Controllers\Admin\UsersController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [\App\Http\Controllers\Admin\UsersController::class, 'destroy'])->name('users.destroy');
    });
});
