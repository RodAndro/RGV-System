<?php

use App\Http\Middleware\CamelCaseJsonResponse;
use App\Http\Middleware\EnsureActiveEmployee;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsEmployee;
use App\Http\Middleware\LogApiRequest;
use App\Http\Middleware\LogPageVisit;
use App\Http\Middleware\RequireMfa;
use App\Http\Middleware\RequireTokenAbility;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TieredRateLimitMiddleware;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->replace(
            TrustProxies::class,
            App\Http\Middleware\TrustProxies::class
        );

        $middleware->alias([
            'admin' => IsAdmin::class,
            'employee' => IsEmployee::class,
            'api.rate' => TieredRateLimitMiddleware::class,
            'camel.json' => CamelCaseJsonResponse::class,
            'mfa' => RequireMfa::class,
            'active.employee' => EnsureActiveEmployee::class,
            'token.ability' => RequireTokenAbility::class,
        ]);

        $middleware->append(SecurityHeaders::class);
        $middleware->append(ForceHttps::class);
        $middleware->append(LogPageVisit::class);
        $middleware->append(LogApiRequest::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (DomainException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        $exceptions->reportable(function (Throwable $e) {
            $source = str_replace('\\', '/', $e->getFile());
            if ($e instanceof QueryException
                || $e instanceof PDOException
                || str_contains($source, '/Illuminate/Database/Connectors/')) {
                return;
            }

            try {
                Log::error('Application exception.', [
                    'exception' => $e::class,
                    'request_id' => request()->headers->get('X-Request-ID'),
                ]);
            } catch (Throwable) {
            }
        });
    })->create();
