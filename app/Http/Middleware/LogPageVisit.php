<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class LogPageVisit
{
    protected array $excludePaths = ['/_debugbar', '/_debugbar/', '/vendor/', '/api/'];

    public function handle(Request $request, Closure $next): mixed
    {
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $request->server('REQUEST_TIME_FLOAT');
        $response = $next($request);

        if ($request->method() === 'GET' && !$request->ajax() && !$request->expectsJson()) {
            $path = $request->path();
            foreach ($this->excludePaths as $exclude) {
                if (str_starts_with($path, ltrim($exclude, '/'))) {
                    return $response;
                }
            }

            try {
                $responseTimeMs = round((microtime(true) - (float) $startTime) * 1000);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'event' => 'page_visit',
                    'auditable_type' => null,
                    'auditable_id' => null,
                    'old_values' => null,
                    'new_values' => json_encode(['path' => $path, 'method' => $request->method(), 'response_time_ms' => $responseTimeMs]),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Throwable) {}
        }

        return $response;
    }
}
