<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'code' => 'UNAUTHENTICATED',
            ], 401);
        }

        /*
         * Get the latest account status directly
         * from the database.
         */
        $freshUser = $user->fresh();

        if (! $freshUser) {
            return response()->json([
                'message' => 'User account no longer exists.',
                'code' => 'ACCOUNT_NOT_FOUND',
            ], 401);
        }

        if (! (bool) $freshUser->is_active) {
            /*
             * Revoke the current Sanctum token.
             */
            $currentToken = $user->currentAccessToken();

            if ($currentToken) {
                $currentToken->delete();
            }

            return response()->json([
                'message' => 'Your account has been deactivated. Please contact the administrator.',
                'code' => 'ACCOUNT_INACTIVE',
            ], 403);
        }

        return $next($request);
    }
}
