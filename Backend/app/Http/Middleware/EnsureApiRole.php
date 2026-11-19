<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Token-safe role guard for the versioned JSON API.
 *
 * IsAdmin/IsEmployee read the session, which does not exist for a
 * Sanctum bearer-token request. These guards answer with the API error
 * envelope instead of redirecting.
 */
class EnsureApiRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->deny('UNAUTHENTICATED', 'Authentication is required.', 401);
        }

        if (! $user->is_active) {
            return $this->deny('FORBIDDEN', 'This account is inactive.', 403);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        return $this->deny('FORBIDDEN', 'You do not have permission to perform this action.', 403);
    }

    private function deny(string $code, string $message, int $status): Response
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => [],
            ],
        ], $status);
    }
}
