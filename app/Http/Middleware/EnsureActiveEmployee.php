<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Your account is inactive. Contact an administrator.'], 403);
        }

        if (! $user->isEmployee()) {
            return response()->json(['message' => 'This app is for employee accounts only.'], 403);
        }

        return $next($request);
    }
}
