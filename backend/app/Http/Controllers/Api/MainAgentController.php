<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMainAgentRequest;
use App\Http\Requests\UpdateMainAgentRequest;
use App\Models\Agent;
use App\Services\MainAgentService;
use Illuminate\Http\JsonResponse;
use Throwable;

class MainAgentController extends Controller
{
    public function __construct(
        private readonly MainAgentService $mainAgentService
    ) {
    }

    public function store(
        StoreMainAgentRequest $request
    ): JsonResponse {
        try {
            $mainAgent = $this->mainAgentService->create(
                $request->validated()
            );

            return response()->json([
                'message' => 'Main Agent created successfully.',
                'agent' => $mainAgent,
            ], 201);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to create the Main Agent.',
            ], 500);
        }
    }

    public function update(
        UpdateMainAgentRequest $request,
        Agent $mainAgent
    ): JsonResponse {
        if ($mainAgent->agent_type !== 'main_agent') {
            return response()->json([
                'message' => 'The selected record is not a Main Agent.',
            ], 422);
        }

        try {
            $updatedMainAgent =
                $this->mainAgentService->update(
                    $mainAgent,
                    $request->validated()
                );

            return response()->json([
                'message' => 'Main Agent updated successfully.',
                'agent' => $updatedMainAgent,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to update the Main Agent.',
            ], 500);
        }
    }
}
