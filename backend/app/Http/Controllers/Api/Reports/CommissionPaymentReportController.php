<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\AgentCommissionPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionPaymentReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $activeQuery = $this->filteredQuery($request);

        $deletedQuery = $this->filteredQuery($request, true);

        $payments = $this->filteredQuery($request)
            ->with([
                'agent',
                'sale.client',
                'sale.lot.project',
                'createdBy',
            ])
            ->latest('payment_date')
            ->latest('id')
            ->paginate($request->integer('per_page', 10));

        $summary = [
            'total_active_payments' => (clone $activeQuery)->count(),
            'total_active_amount' => (clone $activeQuery)->sum('amount'),

            'total_deleted_payments' => (clone $deletedQuery)->count(),
            'total_deleted_amount' => (clone $deletedQuery)->sum('amount'),

            'net_payment_count' => (clone $activeQuery)->count(),
            'net_payment_amount' => (clone $activeQuery)->sum('amount'),
        ];

        $byAgent = $this->filteredQuery($request)
            ->with('agent')
            ->selectRaw('
                agent_id,
                COUNT(*) as payment_count,
                SUM(amount) as total_amount
            ')
            ->groupBy('agent_id')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($row) {
                return [
                    'agent_id' => $row->agent_id,
                    'agent_code' => $row->agent?->agent_code ?? '—',
                    'agent_name' => $this->agentName($row->agent),
                    'payment_count' => (int) $row->payment_count,
                    'total_amount' => (float) $row->total_amount,
                ];
            });

        $byMethod = $this->filteredQuery($request)
            ->selectRaw('
                payment_method,
                COUNT(*) as payment_count,
                SUM(amount) as total_amount
            ')
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($row) {
                return [
                    'payment_method' => $row->payment_method ?? '—',
                    'payment_count' => (int) $row->payment_count,
                    'total_amount' => (float) $row->total_amount,
                ];
            });

        $deletedPayments = $this->filteredQuery($request, true)
            ->with([
                'agent',
                'sale.client',
                'deletedBy',
            ])
            ->latest('deleted_at')
            ->limit(10)
            ->get();

        return response()->json([
            'summary' => $summary,
            'by_agent' => $byAgent,
            'by_method' => $byMethod,
            'payments' => $payments,
            'deleted_payments' => $deletedPayments,
        ]);
    }

    private function filteredQuery(Request $request, bool $deleted = false)
    {
        $query = AgentCommissionPayment::query();

        if ($deleted) {
            $query->onlyTrashed();
        }

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($query) use ($search) {
                $query->where('reference_no', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereHas('agent', function ($query) use ($search) {
                        $query->where('agent_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale', function ($query) use ($search) {
                        $query->where('sale_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale.client', function ($query) use ($search) {
                        $query->where('client_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    private function agentName($agent): string
    {
        if (! $agent) {
            return '—';
        }

        return trim(
            ($agent->first_name ?? '') . ' ' .
            ($agent->middle_name ?? '') . ' ' .
            ($agent->last_name ?? '')
        ) ?: '—';
    }
}
