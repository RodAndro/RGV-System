<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireMfa
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->mfa_enabled && !session('mfa.verified')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'MFA verification required.'], 403);
            }
            return redirect()->route('mfa.verify');
        }

        return $next($request);
    }
}
