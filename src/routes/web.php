<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\UserAuthController;

Route::get('/', [UserAuthController::class, 'showHome'])->name('home');
Route::post('login', [UserAuthController::class, 'login']);
Route::post('logout', [UserAuthController::class, 'logout'])->name('logout');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/api/user', [UserAuthController::class, 'user']);
});

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
        
        Route::get('areas', [\App\Http\Controllers\Admin\AreasController::class, 'index'])->name('areas');
        Route::get('areas/create', [\App\Http\Controllers\Admin\AreasController::class, 'create'])->name('areas.create');
        Route::post('areas', [\App\Http\Controllers\Admin\AreasController::class, 'store'])->name('areas.store');
        Route::get('areas/{area}/edit', [\App\Http\Controllers\Admin\AreasController::class, 'edit'])->name('areas.edit');
        Route::put('areas/{area}', [\App\Http\Controllers\Admin\AreasController::class, 'update'])->name('areas.update');
        Route::delete('areas/{area}', [\App\Http\Controllers\Admin\AreasController::class, 'destroy'])->name('areas.destroy');
        Route::get('areas/{area}/visits', [\App\Http\Controllers\Admin\AreaVisitsController::class, 'index'])->name('areas.visits');

        Route::get('visits', [\App\Http\Controllers\Admin\VisitsController::class, 'index'])->name('visits');
        Route::get('visits/create', [\App\Http\Controllers\Admin\VisitsController::class, 'create'])->name('visits.create');
        Route::post('visits', [\App\Http\Controllers\Admin\VisitsController::class, 'store'])->name('visits.store');
        Route::get('visits/{visit}/edit', [\App\Http\Controllers\Admin\VisitsController::class, 'edit'])->name('visits.edit');
        Route::put('visits/{visit}', [\App\Http\Controllers\Admin\VisitsController::class, 'update'])->name('visits.update');
        Route::delete('visits/{visit}', [\App\Http\Controllers\Admin\VisitsController::class, 'destroy'])->name('visits.destroy');

        Route::get('buildings', [\App\Http\Controllers\Admin\BuildingsController::class, 'index'])->name('buildings');
        Route::get('buildings/create', [\App\Http\Controllers\Admin\BuildingsController::class, 'create'])->name('buildings.create');
        Route::post('buildings', [\App\Http\Controllers\Admin\BuildingsController::class, 'store'])->name('buildings.store');
        Route::get('buildings/{building}/edit', [\App\Http\Controllers\Admin\BuildingsController::class, 'edit'])->name('buildings.edit');
        Route::put('buildings/{building}', [\App\Http\Controllers\Admin\BuildingsController::class, 'update'])->name('buildings.update');
        Route::delete('buildings/{building}', [\App\Http\Controllers\Admin\BuildingsController::class, 'destroy'])->name('buildings.destroy');
        Route::get('buildings/{building}/rooms', [\App\Http\Controllers\Admin\BuildingsController::class, 'rooms'])->name('buildings.rooms');
        Route::get('buildings/{building}/rooms/create', [\App\Http\Controllers\Admin\BuildingRoomsController::class, 'create'])->name('buildings.rooms.create');
        Route::post('buildings/{building}/rooms', [\App\Http\Controllers\Admin\BuildingRoomsController::class, 'store'])->name('buildings.rooms.store');
        Route::put('buildings/{building}/rooms', [\App\Http\Controllers\Admin\BuildingRoomsController::class, 'update'])->name('buildings.rooms.update');

        Route::get('rooms', [\App\Http\Controllers\Admin\RoomsController::class, 'index'])->name('rooms');
        Route::get('rooms/create', [\App\Http\Controllers\Admin\RoomsController::class, 'create'])->name('rooms.create');
        Route::post('rooms', [\App\Http\Controllers\Admin\RoomsController::class, 'store'])->name('rooms.store');
        Route::get('rooms/{room}/edit', [\App\Http\Controllers\Admin\RoomsController::class, 'edit'])->name('rooms.edit');
        Route::put('rooms/{room}', [\App\Http\Controllers\Admin\RoomsController::class, 'update'])->name('rooms.update');
        Route::delete('rooms/{room}', [\App\Http\Controllers\Admin\RoomsController::class, 'destroy'])->name('rooms.destroy');

        Route::get('admins', [\App\Http\Controllers\Admin\AdminsController::class, 'index'])->name('admins');
        Route::get('admins/create', [\App\Http\Controllers\Admin\AdminsController::class, 'create'])->name('admins.create');
        Route::post('admins', [\App\Http\Controllers\Admin\AdminsController::class, 'store'])->name('admins.store');
        Route::get('admins/{admin}/edit', [\App\Http\Controllers\Admin\AdminsController::class, 'edit'])->name('admins.edit');
        Route::put('admins/{admin}', [\App\Http\Controllers\Admin\AdminsController::class, 'update'])->name('admins.update');
        Route::delete('admins/{admin}', [\App\Http\Controllers\Admin\AdminsController::class, 'destroy'])->name('admins.destroy');
    });
});
