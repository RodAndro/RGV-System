<?php

namespace App\Http\Middleware;

use App\Models\ApiRateLimit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class TieredRateLimitMiddleware
{
    private function limits(): array
    {
        return [
            'public' => (int) env('API_PUBLIC_RATE_LIMIT', 30),
            'standard' => (int) env('API_STANDARD_RATE_LIMIT', 60),
            'premium' => (int) env('API_PREMIUM_RATE_LIMIT', 300),
            'admin' => (int) env('API_ADMIN_RATE_LIMIT', 1000),
            'auth' => 10,
        ];
    }

    public function handle(Request $request, Closure $next, ?string $tier = null): Response
    {
        $tier ??= $this->resolveTier($request);
        $limits = $this->limits();
        $limit = $limits[$tier] ?? $limits['public'];
        $identity = $request->user()?->id ?: $request->ip();
        $key = "api:{$tier}:{$identity}";
        $burstKey = "api-burst:{$tier}:{$identity}";

        if (RateLimiter::tooManyAttempts($burstKey, 5)) {
            return $this->tooMany($request, $tier, $key, $limit);
        }

        RateLimiter::hit($burstKey, 1);

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return $this->tooMany($request, $tier, $key, $limit);
        }

        RateLimiter::hit($key, 60);
        $remaining = RateLimiter::remaining($key, $limit);

        ApiRateLimit::create([
            'user_id' => $request->user()?->id,
            'tier' => $tier,
            'key' => $key,
            'ip_address' => $request->ip(),
            'limit_per_minute' => $limit,
            'remaining' => $remaining,
            'blocked' => false,
            'reset_at' => now()->addSeconds(RateLimiter::availableIn($key) ?: 60),
        ]);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', (string) $limit);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);

        return $response;
    }

    private function tooMany(Request $request, string $tier, string $key, int $limit): Response
    {
        $retryAfter = max(1, RateLimiter::availableIn($key));

        ApiRateLimit::create([
            'user_id' => $request->user()?->id,
            'tier' => $tier,
            'key' => $key,
            'ip_address' => $request->ip(),
            'limit_per_minute' => $limit,
            'remaining' => 0,
            'blocked' => true,
            'reset_at' => now()->addSeconds($retryAfter),
        ]);

        return response()->json([
            'message' => 'Too many requests.',
            'tier' => $tier,
            'retryAfter' => $retryAfter,
        ], 429)->withHeaders([
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => 0,
            'Retry-After' => $retryAfter,
        ]);
    }

    private function resolveTier(Request $request): string
    {
        if ($request->user()?->hasRole('admin')) {
            return 'admin';
        }

        return $request->user()?->api_tier ?? 'public';
    }
}
