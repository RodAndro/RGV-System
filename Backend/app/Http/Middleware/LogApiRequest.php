<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogApiRequest
{
    public function handle(Request $request, Closure $next)
    {
        $startedAt = hrtime(true);
        $response = $next($request);

        if ($request->is('api/*')) {
            Log::info('API request completed.', [
                'method' => $request->method(),
                'path' => '/'.$request->path(),
                'status' => $response->getStatusCode(),
                'user_id' => $request->user()?->id,
                'duration_ms' => (int) round((hrtime(true) - $startedAt) / 1_000_000),
                'request_id' => $request->headers->get('X-Request-ID'),
            ]);
        }

        return $response;
    }
}
