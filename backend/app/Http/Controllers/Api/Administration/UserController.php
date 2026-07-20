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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a paginated list of users.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'role_id' => [
                'nullable',
                'integer',
                'exists:roles,id',
            ],

            'status' => [
                'nullable',
                'in:active,inactive',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'in:10,25,50,100',
            ],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        $perPage =
            (int) ($validated['per_page'] ?? 10);

        $query = User::query()
            ->with([
                'roles:id,name,guard_name',
            ])
            ->latest('id');

        if (!empty($validated['search'])) {
            $search =
                trim($validated['search']);

            $query->where(
                function ($subQuery) use ($search) {
                    $subQuery
                        ->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'email',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        if (!empty($validated['role_id'])) {
            $roleId =
                (int) $validated['role_id'];

            $query->whereHas(
                'roles',
                fn ($roleQuery) =>
                $roleQuery->where(
                    'roles.id',
                    $roleId
                )
            );
        }

        if (
            ($validated['status'] ?? null) ===
            'active'
        ) {
            $query->where('is_active', true);
        }

        if (
            ($validated['status'] ?? null) ===
            'inactive'
        ) {
            $query->where('is_active', false);
        }

        $users = $query
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => UserResource::collection(
                $users->getCollection()
            ),

            'current_page' =>
                $users->currentPage(),

            'last_page' =>
                $users->lastPage(),

            'per_page' =>
                $users->perPage(),

            'total' =>
                $users->total(),

            'from' =>
                $users->firstItem(),

            'to' =>
                $users->lastItem(),

            'summary' => [
                'total_users' =>
                    User::query()->count(),

                'active_users' =>
                    User::query()
                        ->where(
                            'is_active',
                            true
                        )
                        ->count(),

                'inactive_users' =>
                    User::query()
                        ->where(
                            'is_active',
                            false
                        )
                        ->count(),

                'super_admins' =>
                    User::query()
                        ->role('super-admin')
                        ->count(),
            ],
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(
        StoreUserRequest $request
    ): JsonResponse {
        $validated = $request->validated();

        $user = DB::transaction(
            function () use ($validated) {
                $user = User::query()->create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' =>
                        $validated['password'],
                    'is_active' =>
                        $validated['is_active'],
                ]);

                $roles = Role::query()
                    ->whereIn(
                        'id',
                        $validated['roles']
                    )
                    ->where(
                        'guard_name',
                        'web'
                    )
                    ->get();

                $user->syncRoles($roles);

                return $user;
            }
        );

        $user->load([
            'roles:id,name,guard_name',
        ]);

        return response()->json([
            'message' =>
                'User created successfully.',

            'data' =>
                new UserResource($user),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $user->load([
            'roles:id,name,guard_name',
        ]);

        return response()->json([
            'data' =>
                new UserResource($user),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(
        UpdateUserRequest $request,
        User $user
    ): JsonResponse {
        $validated = $request->validated();

        $authenticatedUser =
            $request->user();

        $selectedRoles = Role::query()
            ->whereIn(
                'id',
                $validated['roles']
            )
            ->where(
                'guard_name',
                'web'
            )
            ->get();

        $selectedRoleNames =
            $selectedRoles->pluck('name');

        if (
            $authenticatedUser?->id === $user->id &&
            !$selectedRoleNames->contains(
                'super-admin'
            ) &&
            $user->hasRole('super-admin')
        ) {
            throw ValidationException::withMessages([
                'roles' => [
                    'You cannot remove your own super-admin role.',
                ],
            ]);
        }

        if (
            $authenticatedUser?->id === $user->id &&
            $validated['is_active'] === false
        ) {
            throw ValidationException::withMessages([
                'is_active' => [
                    'You cannot deactivate your own account.',
                ],
            ]);
        }

        DB::transaction(
            function () use (
                $user,
                $validated,
                $selectedRoles
            ) {
                $user->update([
                    'name' =>
                        $validated['name'],

                    'email' =>
                        $validated['email'],

                    'is_active' =>
                        $validated['is_active'],
                ]);

                $user->syncRoles(
                    $selectedRoles
                );
            }
        );

        $user->load([
            'roles:id,name,guard_name',
        ]);

        return response()->json([
            'message' =>
                'User updated successfully.',

            'data' =>
                new UserResource($user),
        ]);
    }

    /**
     * Update account status.
     */
    public function updateStatus(
        UpdateUserStatusRequest $request,
        User $user
    ): JsonResponse {
        $validated = $request->validated();

        if (
            $request->user()?->id === $user->id &&
            $validated['is_active'] === false
        ) {
            throw ValidationException::withMessages([
                'is_active' => [
                    'You cannot deactivate your own account.',
                ],
            ]);
        }

        if (
            $user->hasRole('super-admin') &&
            $validated['is_active'] === false
        ) {
            throw ValidationException::withMessages([
                'is_active' => [
                    'A super-admin account cannot be deactivated.',
                ],
            ]);
        }

        $user->update([
            'is_active' =>
                $validated['is_active'],
        ]);

        $user->load([
            'roles:id,name,guard_name',
        ]);

        return response()->json([
            'message' =>
                $user->is_active
                    ? 'User activated successfully.'
                    : 'User deactivated successfully.',

            'data' =>
                new UserResource($user),
        ]);
    }

    /**
     * Reset a user's password.
     */
    public function resetPassword(
        ResetUserPasswordRequest $request,
        User $user
    ): JsonResponse {
        $user->update([
            'password' =>
                $request->validated('password'),
        ]);

        /*
         * Optional:
         * Revoke all Sanctum tokens after password reset.
         */
        $user->tokens()->delete();

        return response()->json([
            'message' =>
                'Password reset successfully.',
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(
        Request $request,
        User $user
    ): JsonResponse {
        if (
            $request->user()?->id === $user->id
        ) {
            return response()->json([
                'message' =>
                    'You cannot delete your own account.',
            ], 422);
        }

        if ($user->hasRole('super-admin')) {
            return response()->json([
                'message' =>
                    'A super-admin account cannot be deleted.',
            ], 422);
        }

        DB::transaction(
            function () use ($user) {
                $user->syncRoles([]);

                $user->tokens()->delete();

                $user->delete();
            }
        );

        return response()->json([
            'message' =>
                'User deleted successfully.',
        ]);
    }

    /**
     * Return roles for user forms and filters.
     */
    public function options(): JsonResponse
    {
        $roles = Role::query()
            ->select([
                'id',
                'name',
                'guard_name',
            ])
            ->where(
                'guard_name',
                'web'
            )
            ->orderBy('name')
            ->get();

        return response()->json([
            'roles' => $roles,
        ]);
    }
}
