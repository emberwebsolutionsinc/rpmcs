<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyProjectRequest;
use App\Http\Requests\UpdatePropertyProjectRequest;
use App\Models\PropertyProject;
use App\Services\PropertyProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyProjectController extends Controller
{
    public function __construct(
        protected PropertyProjectService $propertyProjectService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $projects = PropertyProject::query()
            ->withCount(['phases', 'blocks', 'lots'])
            ->when($request->search, function ($query, $search) {
                $query->where('project_code', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('developer_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json($projects);
    }

    public function store(StorePropertyProjectRequest $request): JsonResponse
    {
        $project = $this->propertyProjectService->create($request->validated());

        return response()->json([
            'message' => 'Property project created successfully.',
            'data' => $project,
        ], 201);
    }

    public function show(PropertyProject $propertyProject)
{
    $propertyProject->load([
        'phases',
        'blocks',
        'lots',
    ]);

    return response()->json([
        'success' => true,

        'data' => $propertyProject,

        'statistics' => [
            'phases_count' => $propertyProject->phases->count(),
            'blocks_count' => $propertyProject->blocks->count(),
            'lots_count' => $propertyProject->lots->count(),

            'available_lots' => $propertyProject->lots
                ->where('status', 'available')
                ->count(),

            'reserved_lots' => $propertyProject->lots
                ->where('status', 'reserved')
                ->count(),

            'sold_lots' => $propertyProject->lots
                ->where('status', 'sold')
                ->count(),
        ],
    ]);
}

    public function update(UpdatePropertyProjectRequest $request, PropertyProject $propertyProject): JsonResponse
    {
        $project = $this->propertyProjectService->update($propertyProject, $request->validated());

        return response()->json([
            'message' => 'Property project updated successfully.',
            'data' => $project,
        ]);
    }

    public function destroy(PropertyProject $propertyProject): JsonResponse
    {
        $this->propertyProjectService->delete($propertyProject);

        return response()->json([
            'message' => 'Property project deleted successfully.',
        ]);
    }
}
