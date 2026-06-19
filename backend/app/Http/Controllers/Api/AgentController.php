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
                $query->where('agent_code', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

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
        return response()->json([
            'data' => $agent->load(['mainAgent', 'subAgents']),
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
