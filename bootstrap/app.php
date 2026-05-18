<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'employee' => \App\Http\Middleware\IsEmployee::class,
            'api.rate' => \App\Http\Middleware\TieredRateLimitMiddleware::class,
            'camel.json' => \App\Http\Middleware\CamelCaseJsonResponse::class,
            'mfa' => \App\Http\Middleware\RequireMfa::class,
        ]);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\ForceHttps::class);
        $middleware->append(\App\Http\Middleware\LogPageVisit::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Throwable $e) {
            try {
                \App\Models\AuditLog::create([
                    'user_id' => auth()->id(),
                    'event' => 'error',
                    'old_values' => json_encode(['message' => $e->getMessage(), 'code' => $e->getCode()]),
                    'new_values' => json_encode(['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } catch (\Throwable) {}
        });
    })->create();
