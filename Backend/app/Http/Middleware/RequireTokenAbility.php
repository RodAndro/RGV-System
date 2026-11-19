<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTokenAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        abort_unless($request->user()?->tokenCan($ability), 403, 'This token is not authorized for this action.');

        return $next($request);
    }
}
