<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectBlockRequest;
use App\Http\Requests\UpdateProjectBlockRequest;
use App\Models\ProjectBlock;
use App\Services\ProjectBlockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectBlockController extends Controller
{
    public function __construct(
        protected ProjectBlockService $projectBlockService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $blocks = ProjectBlock::query()
            ->with(['project', 'phase'])
            ->withCount(['lots'])
            ->when($request->property_project_id, function ($query, $projectId) {
                $query->where('property_project_id', $projectId);
            })
            ->when($request->project_phase_id, function ($query, $phaseId) {
                $query->where('project_phase_id', $phaseId);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('block_no', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json($blocks);
    }

    public function store(StoreProjectBlockRequest $request): JsonResponse
    {
        $block = $this->projectBlockService->create($request->validated());

        return response()->json([
            'message' => 'Project block created successfully.',
            'data' => $block,
        ], 201);
    }

    public function show(ProjectBlock $projectBlock): JsonResponse
    {
        $projectBlock->load(['project', 'phase', 'lots']);

        return response()->json([
            'data' => $projectBlock,
        ]);
    }

    public function update(UpdateProjectBlockRequest $request, ProjectBlock $projectBlock): JsonResponse
    {
        $block = $this->projectBlockService->update($projectBlock, $request->validated());

        return response()->json([
            'message' => 'Project block updated successfully.',
            'data' => $block,
        ]);
    }

    public function destroy(ProjectBlock $projectBlock): JsonResponse
    {
        $this->projectBlockService->delete($projectBlock);

        return response()->json([
            'message' => 'Project block deleted successfully.',
        ]);
    }
}
