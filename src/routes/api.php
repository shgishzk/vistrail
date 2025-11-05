<?php

use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\MapConfigController;
use App\Http\Controllers\Api\NewsController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::middleware([EnsureFrontendRequestsAreStateful::class, 'auth:sanctum'])->group(function () {
    Route::get('/map/config', MapConfigController::class);
    Route::get('/buildings', [BuildingController::class, 'index']);
    Route::get('/buildings/{building}', [BuildingController::class, 'show']);
    Route::get('/news', NewsController::class);
});
