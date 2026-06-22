<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentSchedule;
use App\Services\PaymentScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OverdueAccountController extends Controller
{
    public function __construct(
        protected PaymentScheduleService $paymentScheduleService
    ) {}

    public function summary(): JsonResponse
    {
        $this->paymentScheduleService->markOverdue();

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return response()->json([
            'data' => [
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

                'due_this_week' => PaymentSchedule::query()
                    ->whereIn('status', [
                        'pending',
                        'partial',
                    ])
                    ->whereBetween('due_date', [
                        $startOfWeek,
                        $endOfWeek,
                    ])
                    ->count(),

                'due_this_month' => PaymentSchedule::query()
                    ->whereIn('status', [
                        'pending',
                        'partial',
                    ])
                    ->whereBetween('due_date', [
                        $startOfMonth,
                        $endOfMonth,
                    ])
                    ->count(),

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

    public function index(Request $request): JsonResponse
    {
        $this->paymentScheduleService->markOverdue();

        $overdues = PaymentSchedule::query()
            ->with([
                'sale.client',
                'sale.lot.project',
                'sale.agent',
            ])
            ->where('status', 'overdue')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('sale', function ($query) use ($search) {
                        $query->where('sale_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.client', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('client_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.lot', function ($query) use ($search) {
                        $query->where('lot_no', 'like', "%{$search}%")
                            ->orWhere('lot_code', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy('due_date', 'asc')
            ->paginate($request->per_page ?? 10);

        $overdues->getCollection()->transform(function ($schedule) {
            $daysOverdue = Carbon::parse($schedule->due_date)
                ->diffInDays(Carbon::today());

            $bucket = '1-30 Days';
            $priority = 'low';

            if ($daysOverdue > 30) {
                $bucket = '31-60 Days';
                $priority = 'medium';
            }

            if ($daysOverdue > 60) {
                $bucket = '61-90 Days';
                $priority = 'high';
            }

            if ($daysOverdue > 90) {
                $bucket = '90+ Days';
                $priority = 'critical';
            }

            $schedule->days_overdue = $daysOverdue;
            $schedule->aging_bucket = $bucket;
            $schedule->collection_priority = $priority;

            return $schedule;
        });

        return response()->json($overdues);
    }

    public function topDelinquents(): JsonResponse
{
    $this->paymentScheduleService->markOverdue();

    $records = PaymentSchedule::query()
        ->with([
            'sale.client',
            'sale.lot.project',
        ])
        ->where('status', 'overdue')
        ->get()
        ->groupBy('sale_id')
        ->map(function ($items) {

            $first = $items->first();

            return [
                'sale_id' => $first->sale_id,
                'sale_no' => $first->sale?->sale_no,

                'client' => $first->sale?->client,

                'project' => $first->sale?->lot?->project?->project_name,

                'lot_no' => $first->sale?->lot?->lot_no,

                'overdue_installments' => $items->count(),

                'total_overdue_balance' => $items->sum('balance'),

                'oldest_due_date' => $items->min('due_date'),
            ];
        })
        ->sortByDesc('total_overdue_balance')
        ->take(10)
        ->values();

    return response()->json([
        'data' => $records,
    ]);
}
}
