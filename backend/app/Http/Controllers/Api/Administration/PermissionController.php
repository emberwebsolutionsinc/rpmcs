<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Permission::query()
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(
                'name',
                'like',
                "%{$search}%"
            );
        }

        $permissions = $query->get()
            ->map(function (Permission $permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                    'module' => $this->extractModule(
                        $permission->name
                    ),
                    'action' => $this->extractAction(
                        $permission->name
                    ),
                    'created_at' => $permission->created_at,
                    'updated_at' => $permission->updated_at,
                ];
            });

        $groupedPermissions = $permissions
            ->groupBy('module')
            ->map(function ($items, $module) {
                return [
                    'module' => $module,
                    'permissions' => $items->values(),
                ];
            })
            ->values();

        return response()->json([
            'data' => $permissions,
            'grouped_permissions' => $groupedPermissions,
            'summary' => [
                'total_permissions' => $permissions->count(),
                'total_modules' => $permissions
                    ->pluck('module')
                    ->unique()
                    ->count(),
            ],
        ]);
    }

    private function extractModule(string $permission): string
    {
        $parts = preg_split(
            '/[.\s_-]+/',
            $permission
        );

        if (!$parts || count($parts) === 0) {
            return 'general';
        }

        return strtolower($parts[0]);
    }

    private function extractAction(string $permission): string
    {
        $parts = preg_split(
            '/[.\s_-]+/',
            $permission
        );

        if (!$parts || count($parts) <= 1) {
            return $permission;
        }

        array_shift($parts);

        return implode(' ', $parts);
    }
}
