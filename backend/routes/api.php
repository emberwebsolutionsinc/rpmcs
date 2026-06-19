<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyProjectController;
use App\Http\Controllers\Api\ProjectPhaseController;
use App\Http\Controllers\Api\ProjectBlockController;
use App\Http\Controllers\Api\LotController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\AgentController;
use App\Models\PropertyType;
use App\Http\Controllers\Api\ReservationController;

Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/property-types', function () {
            return response()->json([
                'data' => PropertyType::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),
            ]);
        });

        Route::prefix('sales-management')->group(function () {
        Route::apiResource('reservations', ReservationController::class);

        Route::patch(
            'reservations/{reservation}/cancel',
            [ReservationController::class, 'cancel']
            );
        });

        Route::prefix('property-management')->group(function () {
            Route::apiResource('property-projects', PropertyProjectController::class);
            Route::apiResource('project-phases', ProjectPhaseController::class);
            Route::apiResource('project-blocks', ProjectBlockController::class);
            Route::apiResource('lots', LotController::class);
        });

        Route::prefix('client-management')->group(function () {
            Route::apiResource('clients', ClientController::class);
        });

        Route::prefix('agent-management')->group(function () {
            Route::apiResource('agents', AgentController::class);
        });
    });
});
