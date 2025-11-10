<?php

use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AreaPickupController;
use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\MapConfigController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\MyAreasController;
use App\Http\Controllers\Api\PinController;
use App\Http\Controllers\Api\VisitActionController;
use App\Http\Controllers\Api\VisitReassignmentController;
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
    Route::get('/areas', [AreaController::class, 'index']);
    Route::get('/areas/{area}', [AreaController::class, 'show'])
        ->whereNumber('area');
    Route::post('/areas/pickup', AreaPickupController::class);
    Route::get('/pins', [PinController::class, 'index']);
    Route::post('/pins', [PinController::class, 'store']);
    Route::put('/pins/{pin}', [PinController::class, 'update']);
    Route::delete('/pins/{pin}', [PinController::class, 'destroy']);
    Route::patch('/visits/{visit}/request-reassignment', [VisitActionController::class, 'requestReassignment'])
        ->whereNumber('visit');
    Route::patch('/visits/{visit}/return-unstarted', [VisitActionController::class, 'returnAsUnstarted'])
        ->whereNumber('visit');
    Route::get('/visits/pending-reassignment', [VisitReassignmentController::class, 'index']);
    Route::post('/visits/{visit}/accept-reassignment', [VisitReassignmentController::class, 'accept'])
        ->whereNumber('visit');
    Route::patch('/visits/{visit}/complete', [VisitActionController::class, 'complete'])
        ->whereNumber('visit');
});
