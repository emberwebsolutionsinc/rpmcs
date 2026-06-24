<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Exports\AgentCommissionReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;


class AgentCommissionReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sales = $this->filteredSales($request)
            ->with([
                'agent',
                'client',
                'lot.project',
            ])
            ->get();

        $agentRows = $sales
            ->groupBy('agent_id')
            ->map(function ($agentSales) {
                $firstSale = $agentSales->first();
                $agent = $firstSale?->agent;

                $commissionRate = (float) ($agent?->default_commission_rate ?? 0);

                $totalContractPrice = $agentSales->sum('contract_price');
                $totalDownpayment = $agentSales->sum('downpayment');
                $totalBalance = $agentSales->sum('balance');

                $commissionEarned = $totalContractPrice * ($commissionRate / 100);
                $commissionPaid = 0;
                $commissionBalance = $commissionEarned - $commissionPaid;

                return [
                    'agent_id' => $agent?->id,
                    'agent_code' => $agent?->agent_code ?? '—',
                    'agent_name' => $this->fullName($agent),
                    'agent_type' => $agent?->agent_type ?? '—',
                    'commission_rate' => $commissionRate,

                    'sales_count' => $agentSales->count(),
                    'total_contract_price' => $totalContractPrice,
                    'total_downpayment' => $totalDownpayment,
                    'total_balance' => $totalBalance,

                    'commission_earned' => $commissionEarned,
                    'commission_paid' => $commissionPaid,
                    'commission_balance' => $commissionBalance,
                ];
            })
            ->values();

        $summary = [
            'total_agents' => $agentRows->count(),
            'total_sales' => $agentRows->sum('sales_count'),
            'total_contract_price' => $agentRows->sum('total_contract_price'),
            'total_downpayment' => $agentRows->sum('total_downpayment'),
            'total_balance' => $agentRows->sum('total_balance'),
            'gross_commission' => $agentRows->sum('commission_earned'),
            'paid_commission' => $agentRows->sum('commission_paid'),
            'unpaid_commission' => $agentRows->sum('commission_balance'),
        ];

        return response()->json([
            'summary' => $summary,
            'agents' => $agentRows,
            'sales' => $this->paginatedSales($request),
        ]);
    }

    private function filteredSales(Request $request)
    {
        $query = Sale::query()
            ->whereNotNull('agent_id')
            ->where('status', '!=', 'cancelled');

        if ($request->filled('from_date')) {
            $query->whereDate(
                'sale_date',
                '>=',
                $request->from_date
            );
        }

        if ($request->filled('to_date')) {
            $query->whereDate(
                'sale_date',
                '<=',
                $request->to_date
            );
        }

        if ($request->filled('agent_id')) {
            $query->where(
                'agent_id',
                $request->agent_id
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($query) use ($search) {
                $query->where('sale_no', 'like', "%{$search}%")
                    ->orWhereHas('agent', function ($query) use ($search) {
                        $query->where('agent_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('client', function ($query) use ($search) {
                        $query->where('client_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('lot', function ($query) use ($search) {
                        $query->where('lot_no', 'like', "%{$search}%")
                            ->orWhere('lot_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('lot.project', function ($query) use ($search) {
                        $query->where('project_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

        public function exportExcel(Request $request): Response
        {
            $filename = 'agent-commission-report-' . now()->format('Y-m-d-His') . '.xlsx';

            return Excel::download(
                new AgentCommissionReportExport($request->all()),
                $filename
            );
        }

public function exportPdf(Request $request)
{
    $sales = $this->filteredSales($request)
        ->with([
            'agent',
            'client',
            'lot.project',
        ])
        ->latest('sale_date')
        ->latest('id')
        ->get();

    $agentRows = $sales
        ->groupBy('agent_id')
        ->map(function ($agentSales) {
            $agent = $agentSales->first()?->agent;

            $commissionRate = (float) ($agent?->default_commission_rate ?? 0);

            $totalContractPrice = $agentSales->sum('contract_price');
            $totalDownpayment = $agentSales->sum('downpayment');
            $totalBalance = $agentSales->sum('balance');

            $commissionEarned = $totalContractPrice * ($commissionRate / 100);

            return [
                'agent_code' => $agent?->agent_code ?? '—',
                'agent_name' => $this->fullName($agent),
                'agent_type' => $agent?->agent_type ?? '—',
                'commission_rate' => $commissionRate,
                'sales_count' => $agentSales->count(),
                'total_contract_price' => $totalContractPrice,
                'total_downpayment' => $totalDownpayment,
                'total_balance' => $totalBalance,
                'commission_earned' => $commissionEarned,
                'commission_paid' => 0,
                'commission_balance' => $commissionEarned,
            ];
        })
        ->values();

    $summary = [
        'total_agents' => $agentRows->count(),
        'total_sales' => $agentRows->sum('sales_count'),
        'total_contract_price' => $agentRows->sum('total_contract_price'),
        'total_downpayment' => $agentRows->sum('total_downpayment'),
        'total_balance' => $agentRows->sum('total_balance'),
        'gross_commission' => $agentRows->sum('commission_earned'),
        'paid_commission' => $agentRows->sum('commission_paid'),
        'unpaid_commission' => $agentRows->sum('commission_balance'),
    ];

    $pdf = Pdf::loadView('pdf.agent-commission-report', [
        'summary' => $summary,
        'agents' => $agentRows,
        'sales' => $sales,
        'filters' => $request->all(),
    ])->setPaper('a4', 'landscape');

    return $pdf->download(
        'agent-commission-report-' . now()->format('Y-m-d-His') . '.pdf'
    );
}

    private function paginatedSales(Request $request)
    {
        $sales = $this->filteredSales($request)
            ->with([
                'agent',
                'client',
                'lot.project',
            ])
            ->latest('sale_date')
            ->latest('id')
            ->paginate($request->integer('per_page', 10));

        $sales->getCollection()->transform(function ($sale) {
            $commissionRate = (float) ($sale->agent?->default_commission_rate ?? 0);
            $commissionEarned = (float) $sale->contract_price * ($commissionRate / 100);

            $sale->commission_rate = $commissionRate;
            $sale->commission_earned = $commissionEarned;
            $sale->commission_paid = 0;
            $sale->commission_balance = $commissionEarned;

            return $sale;
        });

        return $sales;
    }

    private function fullName(?Agent $agent): string
    {
        if (! $agent) {
            return 'Unassigned Agent';
        }

        return trim(
            ($agent->first_name ?? '') . ' ' .
            ($agent->middle_name ?? '') . ' ' .
            ($agent->last_name ?? '')
        ) ?: 'Unnamed Agent';
    }
}
