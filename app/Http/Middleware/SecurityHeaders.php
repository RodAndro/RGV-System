<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $scriptSrc = ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'cdn.tailwindcss.com', 'cdnjs.cloudflare.com'];
        $styleSrc = ["'self'", "'unsafe-inline'", 'cdnjs.cloudflare.com', 'fonts.bunny.net'];
        $fontSrc = ["'self'", 'cdnjs.cloudflare.com', 'fonts.bunny.net'];
        $imgSrc = ["'self'", 'data:'];
        $connectSrc = ["'self'"];

        if (app()->environment('local')) {
            // The Vite dev server may bind to IPv6 or IPv4 localhost.
            $vite = [
                'http://[::1]:5173', 'http://127.0.0.1:5173', 'http://localhost:5173',
                'ws://[::1]:5173', 'ws://127.0.0.1:5173', 'ws://localhost:5173',
            ];
            $scriptSrc = array_merge($scriptSrc, $vite);
            $styleSrc = array_merge($styleSrc, $vite);
            $connectSrc = array_merge($connectSrc, $vite);
        }

        $csp = config('app.csp', sprintf(
            "default-src 'self'; script-src %s; style-src %s; font-src %s; img-src %s; connect-src %s",
            implode(' ', $scriptSrc),
            implode(' ', $styleSrc),
            implode(' ', $fontSrc),
            implode(' ', $imgSrc),
            implode(' ', $connectSrc)
        ));
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
