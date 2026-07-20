<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\StoreRoleRequest;
use App\Http\Requests\Administration\UpdateRoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Role::query()
            ->select('roles.*')
            ->selectSub(
                DB::table('model_has_roles')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn(
                        'model_has_roles.role_id',
                        'roles.id'
                    )
                    ->where(
                        'model_has_roles.model_type',
                        User::class
                    ),
                'users_count'
            )
            ->with([
                'permissions:id,name,guard_name',
            ])
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(
                'name',
                'like',
                "%{$search}%"
            );
        }

        $perPage = max(
            1,
            min(
                (int) $request->input('per_page', 10),
                100
            )
        );

        $roles = $query->paginate($perPage);

        $rolesWithUsers = DB::table('model_has_roles')
            ->where(
                'model_type',
                User::class
            )
            ->distinct()
            ->count('role_id');

        return response()->json([
            'data' => $roles->items(),

            'summary' => [
                'total_roles' => Role::count(),

                'roles_with_users' =>
                    $rolesWithUsers,

                'total_permissions' =>
                    Permission::count(),
            ],

            'current_page' =>
                $roles->currentPage(),

            'last_page' =>
                $roles->lastPage(),

            'per_page' =>
                $roles->perPage(),

            'total' =>
                $roles->total(),
        ]);
    }

    public function store(
        StoreRoleRequest $request
    ): JsonResponse {
        $role = DB::transaction(
            function () use ($request) {
                $role = Role::create([
                    'name' =>
                        $request->validated('name'),

                    'guard_name' =>
                        'web',
                ]);

                $permissionIds =
                    $request->validated(
                        'permissions',
                        []
                    );

                $permissions = Permission::query()
                    ->whereIn(
                        'id',
                        $permissionIds
                    )
                    ->get();

                $role->syncPermissions(
                    $permissions
                );

                return $role->load([
                    'permissions:id,name,guard_name',
                ]);
            }
        );

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        return response()->json([
            'message' =>
                'Role created successfully.',

            'data' =>
                $role,
        ], 201);
    }

    public function show(Role $role): JsonResponse
    {
        $role->load([
            'permissions:id,name,guard_name',
        ]);

        return response()->json([
            'data' => $role,
        ]);
    }

    public function update(
        UpdateRoleRequest $request,
        Role $role
    ): JsonResponse {
        if ($role->name === 'super-admin') {
            return response()->json([
                'message' =>
                    'The super-admin role cannot be modified.',
            ], 422);
        }

        $updatedRole = DB::transaction(
            function () use ($request, $role) {
                $role->update([
                    'name' =>
                        $request->validated('name'),
                ]);

                $permissionIds =
                    $request->validated(
                        'permissions',
                        []
                    );

                $permissions = Permission::query()
                    ->whereIn(
                        'id',
                        $permissionIds
                    )
                    ->get();

                $role->syncPermissions(
                    $permissions
                );

                return $role
                    ->fresh()
                    ->load([
                        'permissions:id,name,guard_name',
                    ]);
            }
        );

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        return response()->json([
            'message' =>
                'Role updated successfully.',

            'data' =>
                $updatedRole,
        ]);
    }

    public function destroy(
        Role $role
    ): JsonResponse {
        if ($role->name === 'super-admin') {
            return response()->json([
                'message' =>
                    'The super-admin role cannot be deleted.',
            ], 422);
        }

        $hasAssignedUsers = DB::table(
            'model_has_roles'
        )
            ->where('role_id', $role->id)
            ->where(
                'model_type',
                User::class
            )
            ->exists();

        if ($hasAssignedUsers) {
            return response()->json([
                'message' =>
                    'This role is assigned to users and cannot be deleted.',
            ], 422);
        }

        DB::transaction(function () use ($role) {
            $role->syncPermissions([]);
            $role->delete();
        });

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        return response()->json([
            'message' =>
                'Role deleted successfully.',
        ]);
    }
}
