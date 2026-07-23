<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\ResetUserPasswordRequest;
use App\Http\Requests\Administration\StoreUserRequest;
use App\Http\Requests\Administration\UpdateUserRequest;
use App\Http\Requests\Administration\UpdateUserStatusRequest;
use App\Http\Resources\Administration\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

use Throwable;


class UserController extends Controller
{
public function index(
    Request $request
): AnonymousResourceCollection {

    $validated = $request->validate([
        'search' => [
            'nullable',
            'string',
            'max:255',
        ],

        'role' => [
            'nullable',
            'string',
            'max:255',
        ],

        'status' => [
            'nullable',
            'in:active,inactive',
        ],

        'sort_by' => [
            'nullable',
            'in:name,email,created_at,updated_at',
        ],

        'sort_direction' => [
            'nullable',
            'in:asc,desc',
        ],

        'per_page' => [
            'nullable',
            'integer',
            'min:5',
            'max:100',
        ],
    ]);

    $search = trim($validated['search'] ?? '');

    $role = $validated['role'] ?? null;

    $status = $validated['status'] ?? null;

    $sortBy = $validated['sort_by'] ?? 'created_at';

    $sortDirection = $validated['sort_direction'] ?? 'desc';

    $perPage = $validated['per_page'] ?? 15;

    $users = User::query()
        ->with([
            'roles:id,name,guard_name',
        ])

        ->when(
            $search !== '',
            function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }
        )

        ->when(
            $role,
            function ($query) use ($role) {
                $query->whereHas('roles', function ($roleQuery) use ($role) {
                    $roleQuery->where('name', $role);
                });
            }
        )

        ->when(
            $status === 'active',
            fn ($query) => $query->where('is_active', true)
        )

        ->when(
            $status === 'inactive',
            fn ($query) => $query->where('is_active', false)
        )

        ->orderBy($sortBy, $sortDirection)

        ->paginate($perPage)

        ->withQueryString();

    return UserResource::collection($users);
}

public function options(
    Request $request
): JsonResponse {

    $roles = Role::query()
        ->where('guard_name', 'web')
        ->orderBy('name')
        ->get([
            'id',
            'name',
        ]);

    return response()->json([
        'roles' => $roles,

        'statuses' => [
            [
                'value' => 'active',
                'label' => 'Active',
            ],
            [
                'value' => 'inactive',
                'label' => 'Inactive',
            ],
        ],
    ]);
}

    public function store(
        StoreUserRequest $request
    ): JsonResponse {
        $validated = $request->validated();

        $user = DB::transaction(
            function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],

                    'email' => $validated['email'],

                    'password' => $validated['password'],

                    'is_active' =>
                        $validated['is_active'] ?? true,
                ]);

                $user->syncRoles(
                    $validated['roles']
                );

                return $user;
            }
        );

        $user->load([
            'roles:id,name,guard_name',
        ]);

        return response()->json([
            'message' => 'User created successfully.',

            'user' => new UserResource($user),
        ], 201);
    }

public function show(
    Request $request,
    User $user
): JsonResponse {

    $user->load([
        'roles:id,name,guard_name',
    ]);

    return response()->json([
        'user' => new UserResource($user),
    ]);
}

    public function update(
        UpdateUserRequest $request,
        User $user
    ): JsonResponse {
        $validated =
            $request->validated();

        try {
            DB::beginTransaction();

            $user->update([
                'name' =>
                    $validated[
                        'name'
                    ],

                'email' =>
                    $validated[
                        'email'
                    ],

                'is_active' =>
                    (bool) $validated[
                        'is_active'
                    ],
            ]);

            $roles = Role::query()
                ->whereIn(
                    'id',
                    $validated[
                        'roles'
                    ]
                )
                ->get();

            $user->syncRoles(
                $roles
            );

            DB::commit();

            $updatedUser =
                $user
                    ->fresh()
                    ->load('roles');

            return response()->json([
                'message' =>
                    'User updated successfully.',

                'data' =>
                    new UserResource(
                        $updatedUser
                    ),
            ]);
        } catch (Throwable $error) {
            DB::rollBack();

            report($error);

            return response()->json([
                'message' =>
                    'Failed to update the user.',

                'error' =>
                    config(
                        'app.debug'
                    )
                        ? $error->getMessage()
                        : null,
            ], 500);
        }
    }

    public function updateStatus(
    UpdateUserStatusRequest $request,
    User $user
): JsonResponse {
    $authenticatedUser = $request->user();

    $isActive = $request->boolean(
        'is_active'
    );

    /*
     * Prevent the authenticated user from
     * deactivating their own account.
     */
    if (
        $authenticatedUser->is($user) &&
        ! $isActive
    ) {
        return response()->json([
            'message' =>
                'You cannot deactivate your own account.',
        ], 422);
    }

    /*
     * Determine which permission is required.
     */
    $requiredPermission = $isActive
        ? 'administration.users.activate'
        : 'administration.users.deactivate';

    /*
     * Super Administrator bypass.
     */
    $isSuperAdministrator =
        $authenticatedUser->hasRole(
            'Super Administrator'
        );

    if (
        ! $isSuperAdministrator &&
        ! $authenticatedUser->can(
            $requiredPermission
        )
    ) {
        return response()->json([
            'message' => $isActive
                ? 'You are not authorized to activate users.'
                : 'You are not authorized to deactivate users.',

            'code' => 'USER_STATUS_FORBIDDEN',

            'required_permission' =>
                $requiredPermission,
        ], 403);
    }

    $user->update([
        'is_active' => $isActive,
    ]);

    /*
     * Revoke all tokens when deactivated.
     */
    if (! $isActive) {
        $user->tokens()->delete();
    }

    $user->load([
        'roles:id,name,guard_name',
    ]);

    return response()->json([
        'message' => $isActive
            ? 'User activated successfully.'
            : 'User deactivated successfully.',

        'user' => new UserResource(
            $user
        ),
    ]);
}

    public function resetPassword(
        ResetUserPasswordRequest $request,
        User $user
    ): JsonResponse {
        $validated = $request->validated();

        $user->update([
            'password' => $validated['password'],
        ]);

        /*
         * Revoke all tokens belonging to the user
         * whose password was reset.
         */
        $user->tokens()->delete();

        return response()->json([
            'message' =>
                'User password reset successfully.',
        ]);
    }
}
