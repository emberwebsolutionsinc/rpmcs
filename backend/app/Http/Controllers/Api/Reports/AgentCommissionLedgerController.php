<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentCommissionPayment;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Exports\AgentCommissionLedgerExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class AgentCommissionLedgerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agent_id' => [
                'required',
                'exists:agents,id',
            ],
            'from_date' => [
                'nullable',
                'date',
            ],
            'to_date' => [
                'nullable',
                'date',
            ],
        ]);

        $agent = Agent::query()
            ->findOrFail($validated['agent_id']);

        $sales = Sale::query()
            ->with([
                'client',
                'lot.project',
            ])
            ->where('agent_id', $agent->id)
            ->where('status', '!=', 'cancelled')
            ->when(
                $request->filled('from_date'),
                fn ($query) => $query->whereDate(
                    'sale_date',
                    '>=',
                    $request->from_date
                )
            )
            ->when(
                $request->filled('to_date'),
                fn ($query) => $query->whereDate(
                    'sale_date',
                    '<=',
                    $request->to_date
                )
            )
            ->latest('sale_date')
            ->latest('id')
            ->get();

        $ledgerRows = $sales->map(function ($sale) use ($agent) {
            $commissionRate = (float) ($agent->default_commission_rate ?? 0);
            $commissionEarned = (float) $sale->contract_price * ($commissionRate / 100);

            $commissionPaid = AgentCommissionPayment::query()
                ->where('sale_id', $sale->id)
                ->sum('amount');

            $commissionDeleted = AgentCommissionPayment::onlyTrashed()
                ->where('sale_id', $sale->id)
                ->sum('amount');

            return [
                'sale_id' => $sale->id,
                'sale_no' => $sale->sale_no,
                'sale_date' => $sale->sale_date,
                'client' => $sale->client,
                'project' => $sale->lot?->project,
                'lot' => $sale->lot,
                'contract_price' => (float) $sale->contract_price,
                'downpayment' => (float) $sale->downpayment,
                'balance' => (float) $sale->balance,
                'commission_rate' => $commissionRate,
                'commission_earned' => $commissionEarned,
                'commission_paid' => (float) $commissionPaid,
                'commission_deleted' => (float) $commissionDeleted,
                'commission_balance' => max($commissionEarned - $commissionPaid, 0),
                'status' => $sale->status,
            ];
        });

        $payments = AgentCommissionPayment::query()
            ->with([
                'sale.client',
                'sale.lot.project',
                'createdBy',
            ])
            ->where('agent_id', $agent->id)
            ->when(
                $request->filled('from_date'),
                fn ($query) => $query->whereDate(
                    'payment_date',
                    '>=',
                    $request->from_date
                )
            )
            ->when(
                $request->filled('to_date'),
                fn ($query) => $query->whereDate(
                    'payment_date',
                    '<=',
                    $request->to_date
                )
            )
            ->latest('payment_date')
            ->latest('id')
            ->get();

        $deletedPayments = AgentCommissionPayment::onlyTrashed()
            ->with([
                'sale.client',
                'sale.lot.project',
                'deletedBy',
            ])
            ->where('agent_id', $agent->id)
            ->latest('deleted_at')
            ->get();

        $summary = [
            'agent_id' => $agent->id,
            'agent_code' => $agent->agent_code,
            'agent_name' => $this->agentName($agent),
            'agent_type' => $agent->agent_type,
            'commission_rate' => (float) ($agent->default_commission_rate ?? 0),

            'total_sales' => $ledgerRows->count(),
            'total_contract_price' => $ledgerRows->sum('contract_price'),
            'total_downpayment' => $ledgerRows->sum('downpayment'),
            'total_sale_balance' => $ledgerRows->sum('balance'),

            'total_commission_earned' => $ledgerRows->sum('commission_earned'),
            'total_commission_paid' => $ledgerRows->sum('commission_paid'),
            'total_commission_deleted' => $ledgerRows->sum('commission_deleted'),
            'total_commission_balance' => $ledgerRows->sum('commission_balance'),
        ];

        return response()->json([
            'agent' => $agent,
            'summary' => $summary,
            'ledger' => $ledgerRows,
            'payments' => $payments,
            'deleted_payments' => $deletedPayments,
        ]);
    }

    private function agentName(Agent $agent): string
    {
        return trim(
            ($agent->first_name ?? '') . ' ' .
            ($agent->middle_name ?? '') . ' ' .
            ($agent->last_name ?? '')
        ) ?: '—';
    }

    public function exportExcel(Request $request): Response
{
    $request->validate([
        'agent_id' => [
            'required',
            'exists:agents,id',
        ],
    ]);

    $filename = 'agent-commission-ledger-' . now()->format('Y-m-d-His') . '.xlsx';

    return Excel::download(
        new AgentCommissionLedgerExport($request->all()),
        $filename
    );
}

public function exportPdf(Request $request)
{
    $validated = $request->validate([
        'agent_id' => [
            'required',
            'exists:agents,id',
        ],
        'from_date' => [
            'nullable',
            'date',
        ],
        'to_date' => [
            'nullable',
            'date',
        ],
    ]);

    $agent = Agent::query()
        ->findOrFail($validated['agent_id']);

    $sales = Sale::query()
        ->with([
            'client',
            'lot.project',
        ])
        ->where('agent_id', $agent->id)
        ->where('status', '!=', 'cancelled')
        ->when(
            $request->filled('from_date'),
            fn ($query) => $query->whereDate('sale_date', '>=', $request->from_date)
        )
        ->when(
            $request->filled('to_date'),
            fn ($query) => $query->whereDate('sale_date', '<=', $request->to_date)
        )
        ->latest('sale_date')
        ->latest('id')
        ->get();

    $ledgerRows = $sales->map(function ($sale) use ($agent) {
        $commissionRate = (float) ($agent->default_commission_rate ?? 0);
        $commissionEarned = (float) $sale->contract_price * ($commissionRate / 100);

        $commissionPaid = AgentCommissionPayment::query()
            ->where('sale_id', $sale->id)
            ->sum('amount');

        $commissionDeleted = AgentCommissionPayment::onlyTrashed()
            ->where('sale_id', $sale->id)
            ->sum('amount');

        return [
            'sale_id' => $sale->id,
            'sale_no' => $sale->sale_no,
            'sale_date' => $sale->sale_date,
            'client' => $sale->client,
            'project' => $sale->lot?->project,
            'lot' => $sale->lot,
            'contract_price' => (float) $sale->contract_price,
            'downpayment' => (float) $sale->downpayment,
            'balance' => (float) $sale->balance,
            'commission_rate' => $commissionRate,
            'commission_earned' => $commissionEarned,
            'commission_paid' => (float) $commissionPaid,
            'commission_deleted' => (float) $commissionDeleted,
            'commission_balance' => max($commissionEarned - $commissionPaid, 0),
            'status' => $sale->status,
        ];
    });

    $payments = AgentCommissionPayment::query()
        ->with([
            'sale.client',
            'sale.lot.project',
            'createdBy',
        ])
        ->where('agent_id', $agent->id)
        ->when(
            $request->filled('from_date'),
            fn ($query) => $query->whereDate('payment_date', '>=', $request->from_date)
        )
        ->when(
            $request->filled('to_date'),
            fn ($query) => $query->whereDate('payment_date', '<=', $request->to_date)
        )
        ->latest('payment_date')
        ->latest('id')
        ->get();

    $deletedPayments = AgentCommissionPayment::onlyTrashed()
        ->with([
            'sale.client',
            'sale.lot.project',
            'deletedBy',
        ])
        ->where('agent_id', $agent->id)
        ->latest('deleted_at')
        ->get();

    $summary = [
        'agent_id' => $agent->id,
        'agent_code' => $agent->agent_code,
        'agent_name' => $this->agentName($agent),
        'agent_type' => $agent->agent_type,
        'commission_rate' => (float) ($agent->default_commission_rate ?? 0),

        'total_sales' => $ledgerRows->count(),
        'total_contract_price' => $ledgerRows->sum('contract_price'),
        'total_downpayment' => $ledgerRows->sum('downpayment'),
        'total_sale_balance' => $ledgerRows->sum('balance'),

        'total_commission_earned' => $ledgerRows->sum('commission_earned'),
        'total_commission_paid' => $ledgerRows->sum('commission_paid'),
        'total_commission_deleted' => $ledgerRows->sum('commission_deleted'),
        'total_commission_balance' => $ledgerRows->sum('commission_balance'),
    ];

    $pdf = Pdf::loadView('pdf.agent-commission-ledger', [
        'summary' => $summary,
        'ledger' => $ledgerRows,
        'payments' => $payments,
        'deletedPayments' => $deletedPayments,
        'filters' => $request->all(),
    ])->setPaper('a4', 'landscape');

    return $pdf->download(
        'agent-commission-ledger-' . now()->format('Y-m-d-His') . '.pdf'
    );
}
}
