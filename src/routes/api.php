<?php

use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\MapConfigController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\MyAreasController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::get('/settings/public', [SettingController::class, 'publicIndex']);

Route::middleware([EnsureFrontendRequestsAreStateful::class, 'auth:sanctum'])->group(function () {
    Route::get('/map/config', MapConfigController::class);
    Route::get('/buildings', [BuildingController::class, 'index']);
    Route::get('/buildings/search', [BuildingController::class, 'search']);
    Route::get('/buildings/{building}', [BuildingController::class, 'show']);
    Route::patch('/buildings/{building}/rooms/{room}', [BuildingController::class, 'updateRoomStatus']);
    Route::patch('/buildings/{building}/rooms/{room}/touch', [BuildingController::class, 'touchRoom']);
    Route::get('/news', NewsController::class);
    Route::get('/groups', [GroupController::class, 'index']);
    Route::get('/groups/buildings', [GroupController::class, 'buildings']);
    Route::get('/settings', [SettingController::class, 'index']);
    Route::put('/settings', [SettingController::class, 'update']);
    Route::get('/areas/my', MyAreasController::class);
});
