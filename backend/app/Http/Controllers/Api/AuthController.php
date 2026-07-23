<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

public function login(Request $request): JsonResponse
{
    $credentials = $request->validate([
        'email' => [
            'required',
            'email',
        ],
        'password' => [
            'required',
            'string',
        ],
    ]);

    if (! Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Invalid email or password.',
            'errors' => [
                'email' => [
                    'The provided credentials are incorrect.',
                ],
            ],
        ], 422);
    }

    /** @var User|null $user */
    $user = Auth::user();

    if (! $user) {
        return response()->json([
            'message' => 'Unable to authenticate the user.',
        ], 500);
    }

    if (! $user->is_active) {
        Auth::logout();

        return response()->json([
            'message' =>
                'Your account has been deactivated. Please contact the administrator.',
        ], 403);
    }

    /*
     * Optional:
     * Remove previous API tokens so only the newest login remains active.
     *
     * $user->tokens()->delete();
     */

    $token = $user
        ->createToken('rpmcs-token')
        ->plainTextToken;

    $user->load('roles.permissions');

    return response()->json([
        'message' => 'Login successful.',

        'token' => $token,

        'token_type' => 'Bearer',

        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => (bool) $user->is_active,
            'roles' => $user->getRoleNames()->values(),
            'permissions' => $user
                ->getAllPermissions()
                ->pluck('name')
                ->values(),
        ],
    ]);
}
public function me(
    Request $request
): JsonResponse {
    $user = $request->user();

    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' =>
                (bool) $user->is_active,

            'roles' =>
                $user
                    ->getRoleNames()
                    ->values(),

            'permissions' =>
                $user
                    ->getAllPermissions()
                    ->pluck('name')
                    ->values(),
        ],
    ]);
}

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
