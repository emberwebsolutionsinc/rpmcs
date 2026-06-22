<?php

use Illuminate\Support\Facades\Route;

use App\Models\PropertyType;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;

use App\Http\Controllers\Api\PropertyProjectController;
use App\Http\Controllers\Api\ProjectPhaseController;
use App\Http\Controllers\Api\ProjectBlockController;
use App\Http\Controllers\Api\LotController;

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\AgentController;

use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\PaymentScheduleController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\OfficialReceiptController;

Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/dashboard-summary', [DashboardController::class, 'summary']);

        Route::get('/property-types', function () {
            return response()->json([
                'data' => PropertyType::query()
                    ->orderBy('name')
                    ->get(),
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Sales Management
        |--------------------------------------------------------------------------
        */

        Route::prefix('sales-management')->group(function () {

            Route::get(
                'collections/{collection}/print-or',
                [OfficialReceiptController::class, 'print']
            );

            Route::apiResource(
                'reservations',
                ReservationController::class
            );

            Route::patch(
                'reservations/{reservation}/cancel',
                [ReservationController::class, 'cancel']
            );

            Route::get(
                'sales-summary',
                [SaleController::class, 'summary']
            );

            Route::apiResource(
                'sales',
                SaleController::class
            );

            Route::patch(
                'sales/{sale}/cancel',
                [SaleController::class, 'cancel']
            );

            Route::get(
                'payment-schedules',
                [PaymentScheduleController::class, 'index']
            );

            Route::post(
                'payment-schedules/generate',
                [PaymentScheduleController::class, 'generate']
            );

            Route::get(
                'payment-schedules/{paymentSchedule}',
                [PaymentScheduleController::class, 'show']
            );

            Route::delete(
                'payment-schedules/{paymentSchedule}',
                [PaymentScheduleController::class, 'destroy']
            );

            Route::get(
                'collections-summary',
                [CollectionController::class, 'summary']
            );

            Route::get(
                'collections',
                [CollectionController::class, 'index']
            );

            Route::post(
                'collections',
                [CollectionController::class, 'store']
            );

            Route::get(
                'collections/{collection}',
                [CollectionController::class, 'show']
            );

            Route::patch(
                'collections/{collection}/void',
                [CollectionController::class, 'void']
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Property Management
        |--------------------------------------------------------------------------
        */

        Route::prefix('property-management')->group(function () {

            Route::apiResource(
                'property-projects',
                PropertyProjectController::class
            );

            Route::apiResource(
                'project-phases',
                ProjectPhaseController::class
            );

            Route::apiResource(
                'project-blocks',
                ProjectBlockController::class
            );

            Route::apiResource(
                'lots',
                LotController::class
            );

            Route::get('/property-types', function () {
                return response()->json([
                    'data' => PropertyType::query()
                        ->orderBy('name')
                        ->get(),
                ]);
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Client Management
        |--------------------------------------------------------------------------
        */

        Route::prefix('client-management')->group(function () {
            Route::apiResource(
                'clients',
                ClientController::class
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Agent Management
        |--------------------------------------------------------------------------
        */

        Route::prefix('agent-management')->group(function () {
            Route::apiResource(
                'agents',
                AgentController::class
            );
        });
    });
});
