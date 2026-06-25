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
use App\Models\AgentCommissionPayment;

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

        $commissionSales = Sale::query()
            ->with('agent')
            ->whereNotNull('agent_id')
            ->where('status', '!=', 'cancelled')
            ->get();

        $totalCommissionEarned = $commissionSales->sum(function ($sale) {
            $rate = (float) ($sale->agent?->default_commission_rate ?? 0);

            return (float) $sale->contract_price * ($rate / 100);
        });

        $totalCommissionPaid = AgentCommissionPayment::sum('amount');

        $totalCommissionDeleted = AgentCommissionPayment::onlyTrashed()
            ->sum('amount');

        $totalCommissionBalance =
            $totalCommissionEarned - $totalCommissionPaid;

        $topAgent = $commissionSales
            ->groupBy('agent_id')
            ->map(function ($sales) {
                $agent = $sales->first()?->agent;

                $rate = (float) ($agent?->default_commission_rate ?? 0);

                return [
                    'agent_id' => $agent?->id,
                    'agent_code' => $agent?->agent_code,
                    'agent_name' => trim(
                        ($agent?->first_name ?? '') . ' ' .
                        ($agent?->last_name ?? '')
                    ),
                    'commission_earned' => $sales->sum(function ($sale) use ($rate) {
                        return (float) $sale->contract_price * ($rate / 100);
                    }),
                ];
            })
            ->sortByDesc('commission_earned')
            ->first();

        $recentCommissionPayments = AgentCommissionPayment::query()
            ->with([
                'agent',
                'sale.client',
            ])
            ->latest('payment_date')
            ->latest('id')
            ->limit(5)
            ->get();

        return response()->json([
            'commission_summary' => [
                'total_commission_earned' => $totalCommissionEarned,
                'total_commission_paid' => $totalCommissionPaid,
                'total_commission_balance' => $totalCommissionBalance,
                'total_commission_deleted' => $totalCommissionDeleted,
                'top_agent' => $topAgent,
                'recent_payments' => $recentCommissionPayments,
            ],

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
