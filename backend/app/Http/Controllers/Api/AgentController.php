<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Agent;
use App\Models\AgentCommissionPayment;
use App\Models\Sale;
use App\Services\AgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function __construct(
        protected AgentService $agentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $agents = Agent::query()
            ->with(['mainAgent', 'subAgents'])
            ->when(
                $request->agent_type,
                fn ($query, $type) => $query->where('agent_type', $type)
            )
            ->when(
                $request->status,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $request->parent_agent_id,
                fn ($query, $parentId) => $query->where('parent_agent_id', $parentId)
            )
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('agent_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json($agents);
    }

    public function store(StoreAgentRequest $request): JsonResponse
    {
        $agent = $this->agentService->create($request->validated());

        return response()->json([
            'message' => 'Agent created successfully.',
            'data' => $agent->load(['mainAgent', 'subAgents']),
        ], 201);
    }

    public function show(Agent $agent): JsonResponse
    {
        $agent->load([
            'mainAgent',
            'subAgents',
        ]);

        $sales = Sale::query()
            ->with([
                'client',
                'lot.project',
            ])
            ->where('agent_id', $agent->id)
            ->where('status', '!=', 'cancelled')
            ->latest('sale_date')
            ->latest('id')
            ->get();

        $salesLedger = $sales->map(function ($sale) use ($agent) {
            $commissionRate = (float) ($agent->default_commission_rate ?? 0);

            $commissionEarned =
                (float) $sale->contract_price * ($commissionRate / 100);

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
                'status' => $sale->status,

                'client' => $sale->client,
                'lot' => $sale->lot,
                'project' => $sale->lot?->project,

                'contract_price' => (float) $sale->contract_price,
                'downpayment' => (float) $sale->downpayment,
                'balance' => (float) $sale->balance,

                'commission_rate' => $commissionRate,
                'commission_earned' => $commissionEarned,
                'commission_paid' => (float) $commissionPaid,
                'commission_deleted' => (float) $commissionDeleted,
                'commission_balance' => max(
                    $commissionEarned - $commissionPaid,
                    0
                ),
            ];
        });

        $payments = AgentCommissionPayment::query()
            ->with([
                'sale.client',
                'sale.lot.project',
                'createdBy',
            ])
            ->where('agent_id', $agent->id)
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
            'total_sales' => $salesLedger->count(),
            'total_contract_price' => $salesLedger->sum('contract_price'),
            'total_downpayment' => $salesLedger->sum('downpayment'),
            'total_sale_balance' => $salesLedger->sum('balance'),

            'total_commission_earned' => $salesLedger->sum('commission_earned'),
            'total_commission_paid' => $salesLedger->sum('commission_paid'),
            'total_commission_deleted' => $salesLedger->sum('commission_deleted'),
            'total_commission_balance' => $salesLedger->sum('commission_balance'),

            'sub_agents_count' => $agent->subAgents->count(),
        ];

        return response()->json([
            'data' => $agent,
            'summary' => $summary,
            'sales' => $salesLedger,
            'payments' => $payments,
            'deleted_payments' => $deletedPayments,
            'sub_agents' => $agent->subAgents,
        ]);
    }

    public function update(UpdateAgentRequest $request, Agent $agent): JsonResponse
    {
        $agent = $this->agentService->update($agent, $request->validated());

        return response()->json([
            'message' => 'Agent updated successfully.',
            'data' => $agent->load(['mainAgent', 'subAgents']),
        ]);
    }

    public function destroy(Agent $agent): JsonResponse
    {
        $this->agentService->delete($agent);

        return response()->json([
            'message' => 'Agent deleted successfully.',
        ]);
    }
}
