<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Exports\AgingReportExport;
use App\Models\PaymentSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;



class AgingReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $this->filteredQuery($request);

        $records = $query
            ->orderBy('due_date')
            ->paginate($request->integer('per_page', 10));

        $records->getCollection()->transform(function ($schedule) {
            return $this->transformSchedule($schedule);
        });

        return response()->json([
            'summary' => $this->summary($request),
            'buckets' => $this->bucketSummary($request),
            'schedules' => $records,
        ]);
    }

  public function exportExcel(Request $request)
{
    $filename = 'aging-report-' . now()->format('Y-m-d-His') . '.xlsx';

    return Excel::download(
        new AgingReportExport($request->all()),
        $filename
    );
}

public function exportPdf(Request $request)
{
    $records = $this->filteredQuery($request)
        ->orderBy('due_date')
        ->get()
        ->map(function ($schedule) {
            return $this->transformSchedule($schedule);
        });

    $pdf = Pdf::loadView('pdf.aging-report', [
        'records' => $records,
        'summary' => $this->summary($request),
        'buckets' => $this->bucketSummary($request),
        'filters' => $request->all(),
    ])->setPaper('a4', 'landscape');

    return $pdf->download(
        'aging-report-' . now()->format('Y-m-d-His') . '.pdf'
    );
}

    private function filteredQuery(Request $request)
    {
        $query = PaymentSchedule::query()
            ->with([
                'sale.client',
                'sale.agent',
                'sale.lot.project',
                'sale.lot.phase',
                'sale.lot.block',
            ])
            ->where('balance', '>', 0)
            ->whereNotIn('status', [
                'paid',
                'cancelled',
            ]);

        if ($request->filled('from_date')) {
            $query->whereDate('due_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('due_date', '<=', $request->to_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('bucket')) {
            $this->applyBucketFilter($query, $request->bucket);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($query) use ($search) {
                $query->whereHas('sale', function ($query) use ($search) {
                    $query->where('sale_no', 'like', "%{$search}%");
                })
                    ->orWhereHas('sale.client', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('client_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.agent', function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('agent_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.lot', function ($query) use ($search) {
                        $query->where('lot_no', 'like', "%{$search}%")
                            ->orWhere('lot_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.lot.project', function ($query) use ($search) {
                        $query->where('project_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    private function summary(Request $request): array
    {
        $records = $this->filteredQuery($request)->get();

        return [
            'total_accounts' => $records
                ->pluck('sale_id')
                ->unique()
                ->count(),

            'total_installments' => $records->count(),

            'total_balance' => $records->sum('balance'),

            'current_amount' => $records
                ->filter(function ($schedule) {
                    return $this->daysLate($schedule->due_date) <= 0;
                })
                ->sum('balance'),

            'overdue_amount' => $records
                ->filter(function ($schedule) {
                    return $this->daysLate($schedule->due_date) > 0;
                })
                ->sum('balance'),

            'critical_amount' => $records
                ->filter(function ($schedule) {
                    return $this->daysLate($schedule->due_date) >= 181;
                })
                ->sum('balance'),
        ];
    }

    private function bucketSummary(Request $request): array
    {
        $records = $this->filteredQuery($request)->get();

        $buckets = [
            'current' => [
                'label' => 'Current',
                'count' => 0,
                'amount' => 0,
            ],
            '1_30' => [
                'label' => '1-30 Days',
                'count' => 0,
                'amount' => 0,
            ],
            '31_60' => [
                'label' => '31-60 Days',
                'count' => 0,
                'amount' => 0,
            ],
            '61_90' => [
                'label' => '61-90 Days',
                'count' => 0,
                'amount' => 0,
            ],
            '91_180' => [
                'label' => '91-180 Days',
                'count' => 0,
                'amount' => 0,
            ],
            '181_plus' => [
                'label' => '181+ Days',
                'count' => 0,
                'amount' => 0,
            ],
        ];

        foreach ($records as $schedule) {
            $bucket = $this->bucketKey(
                $this->daysLate($schedule->due_date)
            );

            $buckets[$bucket]['count']++;
            $buckets[$bucket]['amount'] += (float) $schedule->balance;
        }

        return $buckets;
    }

    private function transformSchedule(PaymentSchedule $schedule): PaymentSchedule
    {
        $daysLate = $this->daysLate($schedule->due_date);

        $schedule->days_late = $daysLate;
        $schedule->aging_bucket = $this->bucketLabel($daysLate);
        $schedule->aging_bucket_key = $this->bucketKey($daysLate);
        $schedule->risk_level = $this->riskLevel($daysLate);

        return $schedule;
    }

    private function daysLate($dueDate): int
    {
        if (! $dueDate) {
            return 0;
        }

        $due = Carbon::parse($dueDate)->startOfDay();
        $today = Carbon::today();

        if ($due->greaterThanOrEqualTo($today)) {
            return 0;
        }

        return $due->diffInDays($today);
    }

    private function bucketKey(int $daysLate): string
    {
        if ($daysLate <= 0) {
            return 'current';
        }

        if ($daysLate <= 30) {
            return '1_30';
        }

        if ($daysLate <= 60) {
            return '31_60';
        }

        if ($daysLate <= 90) {
            return '61_90';
        }

        if ($daysLate <= 180) {
            return '91_180';
        }

        return '181_plus';
    }

    private function bucketLabel(int $daysLate): string
    {
        return match ($this->bucketKey($daysLate)) {
            'current' => 'Current',
            '1_30' => '1-30 Days',
            '31_60' => '31-60 Days',
            '61_90' => '61-90 Days',
            '91_180' => '91-180 Days',
            '181_plus' => '181+ Days',
            default => 'Current',
        };
    }

    private function riskLevel(int $daysLate): string
    {
        if ($daysLate <= 0) {
            return 'current';
        }

        if ($daysLate <= 30) {
            return 'low';
        }

        if ($daysLate <= 60) {
            return 'medium';
        }

        if ($daysLate <= 90) {
            return 'high';
        }

        return 'critical';
    }

    private function applyBucketFilter($query, string $bucket): void
    {
        $today = Carbon::today();

        match ($bucket) {
            'current' => $query->whereDate('due_date', '>=', $today),

            '1_30' => $query->whereBetween('due_date', [
                $today->copy()->subDays(30),
                $today->copy()->subDay(),
            ]),

            '31_60' => $query->whereBetween('due_date', [
                $today->copy()->subDays(60),
                $today->copy()->subDays(31),
            ]),

            '61_90' => $query->whereBetween('due_date', [
                $today->copy()->subDays(90),
                $today->copy()->subDays(61),
            ]),

            '91_180' => $query->whereBetween('due_date', [
                $today->copy()->subDays(180),
                $today->copy()->subDays(91),
            ]),

            '181_plus' => $query->whereDate(
                'due_date',
                '<=',
                $today->copy()->subDays(181)
            ),

            default => null,
        };
    }
}
