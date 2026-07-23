<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\StoreRoleRequest;
use App\Http\Requests\Administration\UpdateRoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

use Throwable;

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

public function destroy(int $id)
{
    try {
        $role = \Spatie\Permission\Models\Role::findOrFail($id);

        $protectedRoles = [
            'Super Administrator',
            'Administrator',
            'Owner',
            'Accounting',
            'Marketing',
            'Cashier',
            'Encoder',
            'Agent',
        ];

        if (in_array($role->name, $protectedRoles, true)) {
            return response()->json([
                'message' => "The {$role->name} role is a system role and cannot be deleted.",
            ], 422);
        }

        $assignedUsersCount = \Illuminate\Support\Facades\DB::table(
            config('permission.table_names.model_has_roles')
        )
            ->where('role_id', $role->id)
            ->count();

        if ($assignedUsersCount > 0) {
            return response()->json([
                'message' => "This role is assigned to {$assignedUsersCount} user(s). Remove the role from those users before deleting it.",
                'assigned_users_count' => $assignedUsersCount,
            ], 422);
        }

        \Illuminate\Support\Facades\DB::transaction(
            function () use ($role): void {
                $role->syncPermissions([]);
                $role->delete();
            }
        );

        return response()->json([
            'message' => 'Role deleted successfully.',
        ]);
    } catch (
        \Illuminate\Database\Eloquent\ModelNotFoundException $exception
    ) {
        return response()->json([
            'message' => 'Role not found.',
        ], 404);
    } catch (\Throwable $exception) {
        return response()->json([
            'message' => 'Unable to delete the role.',
            'error' => config('app.debug')
                ? $exception->getMessage()
                : null,
        ], 500);
    }
}
}
