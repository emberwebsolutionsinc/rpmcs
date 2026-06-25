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
use App\Http\Controllers\Api\OverdueAccountController;

use App\Http\Controllers\Api\Reports\ReportDashboardController;
use App\Http\Controllers\Api\Reports\CollectionReportController;
use App\Http\Controllers\Api\Reports\SaleReportController;
use App\Http\Controllers\Api\Reports\AgingReportController;
use App\Http\Controllers\Api\Reports\CommissionPaymentReportController;

use App\Http\Controllers\Api\AgentCommissionPaymentController;
use App\Http\Controllers\Api\Reports\AgentCommissionReportController;
use App\Http\Controllers\Api\CommissionPaymentVoucherController;



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
        | Reports
        |--------------------------------------------------------------------------
        */

        Route::prefix('reports')->group(function () {
            Route::get(
                'dashboard',
                [ReportDashboardController::class, 'index']
            );

            /*
            |--------------------------------------------------------------------------
            | Collection Reports
            |--------------------------------------------------------------------------
            */

            Route::get(
                'collections/export-excel',
                [CollectionReportController::class, 'exportExcel']
            );

            Route::get(
                'collections/export-pdf',
                [CollectionReportController::class, 'exportPdf']
            );

            Route::get(
                'collections',
                [CollectionReportController::class, 'index']
            );

            /*
            |--------------------------------------------------------------------------
            | Sales Reports
            |--------------------------------------------------------------------------
            */

            Route::get(
                'sales/export-excel',
                [SaleReportController::class, 'exportExcel']
            );

            Route::get(
                'sales/export-pdf',
                [SaleReportController::class, 'exportPdf']
            );

            Route::get(
                'sales',
                [SaleReportController::class, 'index']
            );

            /*
            |--------------------------------------------------------------------------
            | Aging Reports
            |--------------------------------------------------------------------------
            */

            Route::get(
                'aging/export-excel',
                [AgingReportController::class, 'exportExcel']
            );

            Route::get(
                'aging/export-pdf',
                [AgingReportController::class, 'exportPdf']
            );

            Route::get(
                'aging',
                [AgingReportController::class, 'index']
            );

            /*
            |--------------------------------------------------------------------------
            | Agent Commission Reports
            |--------------------------------------------------------------------------
            */

            Route::get(
                'agent-commissions',
                [AgentCommissionReportController::class, 'index']
                            );

                 /*
                |--------------------------------------------------------------------------
                | Agent Commission Reports
                |--------------------------------------------------------------------------
                */

                Route::get(
                    'agent-commissions/export-excel',
                    [AgentCommissionReportController::class, 'exportExcel']
                );

                Route::get(
                    'agent-commissions/export-pdf',
                    [AgentCommissionReportController::class, 'exportPdf']
                );

                Route::get(
                    'agent-commissions',
                    [AgentCommissionReportController::class, 'index']
                );

  /*
        |--------------------------------------------------------------------------
        | Commission Payment Reports
        |--------------------------------------------------------------------------
        */
                Route::get(
                'commission-payments',
                [CommissionPaymentReportController::class, 'index']
            );

            Route::get(
                'commission-payments/export-excel',
                [CommissionPaymentReportController::class, 'exportExcel']
            );

            Route::get(
                'commission-payments/export-pdf',
                [CommissionPaymentReportController::class, 'exportPdf']
            );

            Route::get(
                'commission-payments',
                [CommissionPaymentReportController::class, 'index']
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Sales Management
        |--------------------------------------------------------------------------
        */

        Route::prefix('sales-management')->group(function () {
            Route::get(
                'top-delinquents',
                [OverdueAccountController::class, 'topDelinquents']
            );

            Route::get(
                'collections/{collection}/print-or',
                [OfficialReceiptController::class, 'print']
            );

            Route::get(
                'overdue-accounts-summary',
                [OverdueAccountController::class, 'summary']
            );

            Route::get(
                'overdue-accounts',
                [OverdueAccountController::class, 'index']
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

            Route::get(
                'agent-commission-payments/{payment}/voucher',
                [CommissionPaymentVoucherController::class, 'print']
            );


            /*
            |--------------------------------------------------------------------------
            | Agent Commission Payments
            |--------------------------------------------------------------------------
            */

            Route::get(
                'agent-commission-payments',
                [AgentCommissionPaymentController::class, 'index']
            );

            Route::post(
                'agent-commission-payments',
                [AgentCommissionPaymentController::class, 'store']
            );

            Route::delete(
                'agent-commission-payments/{agentCommissionPayment}',
                [AgentCommissionPaymentController::class, 'destroy']
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
