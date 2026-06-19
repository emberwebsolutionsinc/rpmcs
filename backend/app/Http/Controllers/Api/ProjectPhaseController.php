<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectPhaseRequest;
use App\Http\Requests\UpdateProjectPhaseRequest;
use App\Models\ProjectPhase;
use App\Services\ProjectPhaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectPhaseController extends Controller
{
    public function __construct(
        protected ProjectPhaseService $projectPhaseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $phases = ProjectPhase::query()
            ->with(['project'])
            ->withCount(['blocks', 'lots'])
            ->when($request->property_project_id, function ($query, $projectId) {
                $query->where('property_project_id', $projectId);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('phase_code', 'like', "%{$search}%")
                    ->orWhere('phase_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json($phases);
    }

    public function store(StoreProjectPhaseRequest $request): JsonResponse
    {
        $phase = $this->projectPhaseService->create($request->validated());

        return response()->json([
            'message' => 'Project phase created successfully.',
            'data' => $phase,
        ], 201);
    }

    public function show(ProjectPhase $projectPhase): JsonResponse
    {
        $projectPhase->load(['project', 'blocks', 'lots']);

        return response()->json([
            'data' => $projectPhase,
        ]);
    }

    public function update(UpdateProjectPhaseRequest $request, ProjectPhase $projectPhase): JsonResponse
    {
        $phase = $this->projectPhaseService->update($projectPhase, $request->validated());

        return response()->json([
            'message' => 'Project phase updated successfully.',
            'data' => $phase,
        ]);
    }

    public function destroy(ProjectPhase $projectPhase): JsonResponse
    {
        $this->projectPhaseService->delete($projectPhase);

        return response()->json([
            'message' => 'Project phase deleted successfully.',
        ]);
    }
}
