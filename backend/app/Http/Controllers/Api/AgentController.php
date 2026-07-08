<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Agent;
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
            ->when($request->agent_type, fn ($query, $type) => $query->where('agent_type', $type))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->parent_agent_id, fn ($query, $parentId) => $query->where('parent_agent_id', $parentId))
            ->when($request->search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('agent_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json($agents);
    }

    public function store(StoreAgentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (! empty($validated['parent_agent_id'])) {
            $parentAgent = Agent::query()->findOrFail($validated['parent_agent_id']);

            if ($parentAgent->agent_type === 'sub_agent') {
                return response()->json([
                    'message' => 'A sub-agent cannot have another sub-agent under them.',
                    'errors' => [
                        'parent_agent_id' => [
                            'Only main agents may have sub-agents.',
                        ],
                    ],
                ], 422);
            }
        }

        $agent = $this->agentService->create($validated);

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
        'documents.uploadedBy',
        'commissionPayments',
        'activities.user',
    ]);

    $sales = $agent->sales()
        ->with([
            'client',
            'reservation',
            'lot.block.phase.project',
            'agentCommissionPayments',
            'collections',
        ])
        ->latest()
        ->get()
        ->map(function ($sale) {
            $contractPrice = (float) ($sale->contract_price ?? 0);

            $commissionPaid = (float) $sale->agentCommissionPayments->sum('amount');

            $totalCollection = (float) $sale->collections->sum('amount');

            $collectionBalance = max($contractPrice - $totalCollection, 0);

            $collectionProgress = $contractPrice > 0
                ? round(($totalCollection / $contractPrice) * 100, 2)
                : 0;

            $commissionRate = (float) ($sale->agent?->default_commission_rate ?? 0);

            $commissionEarned = $commissionRate > 0
                ? round($contractPrice * ($commissionRate / 100), 2)
                : 0;

            $commissionBalance = max($commissionEarned - $commissionPaid, 0);

            return [
                'id' => $sale->id,
                'sale_id' => $sale->id,
                'sale_no' => $sale->sale_no,
                'reservation_id' => $sale->reservation_id,
                'client_id' => $sale->client_id,
                'lot_id' => $sale->lot_id,
                'agent_id' => $sale->agent_id,

                'contract_price' => $contractPrice,
                'downpayment' => (float) ($sale->downpayment ?? 0),
                'balance' => (float) ($sale->balance ?? $collectionBalance),

                'total_collection' => $totalCollection,
                'collection_balance' => $collectionBalance,
                'collection_progress' => min($collectionProgress, 100),

                'sale_date' => $sale->sale_date,
                'status' => $sale->status,
                'remarks' => $sale->remarks,

                'client' => $sale->client,
                'reservation' => $sale->reservation,
                'lot' => $sale->lot,
                'project' => $sale->lot?->block?->phase?->project,
                'phase' => $sale->lot?->block?->phase,
                'block' => $sale->lot?->block,

                'commission_rate' => $commissionRate,
                'commission_earned' => $commissionEarned,
                'commission_paid' => $commissionPaid,
                'commission_balance' => $commissionBalance,
            ];
        });

    $payments = $agent->commissionPayments()
        ->with(['sale', 'createdBy'])
        ->latest()
        ->get();

    $deletedPayments = method_exists($agent, 'deletedCommissionPayments')
        ? $agent->deletedCommissionPayments()
            ->with(['sale', 'deletedBy'])
            ->latest()
            ->get()
        : collect();

    $totalSales = $sales->count();

    $totalContractPrice = $sales->sum('contract_price');

    $totalCollection = $sales->sum('total_collection');

    $totalCollectionBalance = $sales->sum('collection_balance');

    $overallCollectionProgress = $totalContractPrice > 0
        ? round(($totalCollection / $totalContractPrice) * 100, 2)
        : 0;

    $totalCommissionEarned = $sales->sum('commission_earned');

    $totalCommissionPaid = $sales->sum('commission_paid');

    $totalCommissionBalance = max(
        $totalCommissionEarned - $totalCommissionPaid,
        0
    );

    $totalClients = $sales
        ->pluck('client_id')
        ->filter()
        ->unique()
        ->count();

    $totalProjects = $sales
        ->pluck('project.id')
        ->filter()
        ->unique()
        ->count();

    $summary = [
        'total_sales' => $totalSales,
        'total_contract_price' => $totalContractPrice,

        'total_collection' => $totalCollection,
        'total_collection_balance' => $totalCollectionBalance,
        'collection_progress' => min($overallCollectionProgress, 100),

        'total_commission_earned' => $totalCommissionEarned,
        'total_commission_paid' => $totalCommissionPaid,
        'total_commission_balance' => $totalCommissionBalance,

        'total_clients' => $totalClients,
        'total_projects' => $totalProjects,
        'sub_agents_count' => $agent->subAgents->count(),
        'documents_count' => $agent->documents->count(),
    ];

    return response()->json([
        'data' => $agent,
        'summary' => $summary,
        'sales' => $sales,
        'payments' => $payments,
        'deleted_payments' => $deletedPayments,
        'sub_agents' => $agent->subAgents,
        'documents' => $agent->documents,
        'activities' => $agent->activities,
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
