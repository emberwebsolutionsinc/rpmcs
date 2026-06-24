<?php

namespace App\Http\Controllers\Api\Reports;

use App\Exports\AgentCommissionReportExport;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentCommissionPayment;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $agentRows = $this->buildAgentRows($sales);

        return response()->json([
            'summary' => $this->buildSummary($agentRows),
            'agents' => $agentRows,
            'sales' => $this->paginatedSales($request),
        ]);
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

        $agentRows = $this->buildAgentRows($sales);

        $deletedPayments = AgentCommissionPayment::onlyTrashed()
            ->with([
                'agent',
                'sale.client',
                'deletedBy',
            ])
            ->latest('deleted_at')
            ->get();

        $pdf = Pdf::loadView('pdf.agent-commission-report', [
            'summary' => $this->buildSummary($agentRows),
            'agents' => $agentRows,
            'sales' => $sales,
            'deletedPayments' => $deletedPayments,
            'filters' => $request->all(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download(
            'agent-commission-report-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    private function filteredSales(Request $request)
    {
        $query = Sale::query()
            ->whereNotNull('agent_id')
            ->where('status', '!=', 'cancelled');

        if ($request->filled('from_date')) {
            $query->whereDate('sale_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('sale_date', '<=', $request->to_date);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

    private function buildAgentRows($sales)
    {
        return $sales
            ->groupBy('agent_id')
            ->map(function ($agentSales) {
                $agent = $agentSales->first()?->agent;

                $commissionRate = (float) ($agent?->default_commission_rate ?? 0);
                $totalContractPrice = $agentSales->sum('contract_price');
                $totalDownpayment = $agentSales->sum('downpayment');
                $totalBalance = $agentSales->sum('balance');

                $commissionEarned = $totalContractPrice * ($commissionRate / 100);

                $commissionPaid = AgentCommissionPayment::query()
                    ->where('agent_id', $agent?->id)
                    ->sum('amount');

                $commissionDeleted = AgentCommissionPayment::onlyTrashed()
                    ->where('agent_id', $agent?->id)
                    ->sum('amount');

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
                    'commission_deleted' => $commissionDeleted,
                    'commission_balance' => $commissionEarned - $commissionPaid,
                ];
            })
            ->values();
    }

    private function buildSummary($agentRows): array
    {
        return [
            'total_agents' => $agentRows->count(),
            'total_sales' => $agentRows->sum('sales_count'),
            'total_contract_price' => $agentRows->sum('total_contract_price'),
            'total_downpayment' => $agentRows->sum('total_downpayment'),
            'total_balance' => $agentRows->sum('total_balance'),
            'gross_commission' => $agentRows->sum('commission_earned'),
            'paid_commission' => $agentRows->sum('commission_paid'),
            'deleted_commission' => $agentRows->sum('commission_deleted'),
            'unpaid_commission' => $agentRows->sum('commission_balance'),
        ];
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

            $commissionPaid = AgentCommissionPayment::query()
                ->where('sale_id', $sale->id)
                ->sum('amount');

            $commissionDeleted = AgentCommissionPayment::onlyTrashed()
                ->where('sale_id', $sale->id)
                ->sum('amount');

            $sale->commission_rate = $commissionRate;
            $sale->commission_earned = $commissionEarned;
            $sale->commission_paid = $commissionPaid;
            $sale->commission_deleted = $commissionDeleted;
            $sale->commission_balance = $commissionEarned - $commissionPaid;

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
