<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\PaymentSchedule;
use App\Models\Reservation;
use App\Models\Sale;
use App\Services\PaymentScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class ReportDashboardController extends Controller
{
    public function __construct(
        protected PaymentScheduleService $paymentScheduleService
    ) {}

    public function index(): JsonResponse
    {
        $this->paymentScheduleService->markOverdue();

        $today = Carbon::today();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return response()->json([
            'data' => [
                'today_collections' => Collection::query()
                    ->where('status', 'posted')
                    ->whereDate('payment_date', $today)
                    ->sum('amount_paid'),

                'monthly_collections' => Collection::query()
                    ->where('status', 'posted')
                    ->whereBetween('payment_date', [
                        $startOfMonth,
                        $endOfMonth,
                    ])
                    ->sum('amount_paid'),

                'total_collections' => Collection::query()
                    ->where('status', 'posted')
                    ->sum('amount_paid'),

                'voided_collections' => Collection::query()
                    ->where('status', 'voided')
                    ->count(),

                'voided_amount' => Collection::query()
                    ->where('status', 'voided')
                    ->sum('amount_paid'),

                'outstanding_receivables' => Sale::query()
                    ->where('status', '!=', 'cancelled')
                    ->sum('balance'),

                'active_sales' => Sale::query()
                    ->where('status', 'active')
                    ->count(),

                'fully_paid_sales' => Sale::query()
                    ->where('status', 'fully_paid')
                    ->count(),

                'active_reservations' => Reservation::query()
                    ->whereIn('status', [
                        'active',
                        'reserved',
                    ])
                    ->count(),

                'cancelled_reservations' => Reservation::query()
                    ->where('status', 'cancelled')
                    ->count(),

                'overdue_accounts' => PaymentSchedule::query()
                    ->where('status', 'overdue')
                    ->distinct('sale_id')
                    ->count('sale_id'),

                'overdue_installments' => PaymentSchedule::query()
                    ->where('status', 'overdue')
                    ->count(),

                'total_overdue_amount' => PaymentSchedule::query()
                    ->where('status', 'overdue')
                    ->sum('balance'),

                'critical_accounts' => PaymentSchedule::query()
                    ->where('status', 'overdue')
                    ->whereDate(
                        'due_date',
                        '<=',
                        now()->subDays(90)->toDateString()
                    )
                    ->count(),
            ],
        ]);
    }
}
