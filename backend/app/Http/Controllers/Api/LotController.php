<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLotRequest;
use App\Http\Requests\UpdateLotRequest;
use App\Models\Lot;
use App\Services\LotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LotController extends Controller
{
    public function __construct(
        protected LotService $lotService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $lots = Lot::query()
            ->with(['project', 'phase', 'block'])
            ->when($request->property_project_id, function ($query, $projectId) {
                $query->where('property_project_id', $projectId);
            })
            ->when($request->project_phase_id, function ($query, $phaseId) {
                $query->where('project_phase_id', $phaseId);
            })
            ->when($request->project_block_id, function ($query, $blockId) {
                $query->where('project_block_id', $blockId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('lot_code', 'like', "%{$search}%")
                    ->orWhere('lot_no', 'like', "%{$search}%")
                    ->orWhere('title_no', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json($lots);
    }

    public function store(StoreLotRequest $request): JsonResponse
    {
        $lot = $this->lotService->create($request->validated());

        return response()->json([
            'message' => 'Lot created successfully.',
            'data' => $lot,
        ], 201);
    }

    public function show(Lot $lot): JsonResponse
    {
        $lot->load(['project', 'phase', 'block']);

        return response()->json([
            'data' => $lot,
        ]);
    }

    public function update(UpdateLotRequest $request, Lot $lot): JsonResponse
    {
        $lot = $this->lotService->update($lot, $request->validated());

        return response()->json([
            'message' => 'Lot updated successfully.',
            'data' => $lot,
        ]);
    }

    public function destroy(Lot $lot): JsonResponse
    {
        $this->lotService->delete($lot);

        return response()->json([
            'message' => 'Lot deleted successfully.',
        ]);
    }
}
