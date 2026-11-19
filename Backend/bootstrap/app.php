<?php

use App\Http\Middleware\CamelCaseJsonResponse;
use App\Http\Middleware\EnsureActiveEmployee;
use App\Http\Middleware\EnsureApiRole;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsEmployee;
use App\Http\Middleware\LogApiRequest;
use App\Http\Middleware\LogPageVisit;
use App\Http\Middleware\RequireMfa;
use App\Http\Middleware\RequireTokenAbility;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TieredRateLimitMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            'api.role' => EnsureApiRole::class,
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
        /*
        | Consistent JSON error envelope (B5) for the versioned API:
        |
        |   { "error": { "code": "...", "message": "...", "details": [...] } }
        |
        | Scoped to /api/v1 only. The legacy /api/v1/mobile/* endpoints keep
        | Laravel's {message, errors} shape so their existing contract and
        | tests are untouched.
        */
        $wantsEnvelope = static fn ($request): bool => $request->is('api/v1/*')
            && ! $request->is('api/v1/mobile/*');

        $exceptions->renderable(function (ValidationException $e, $request) use ($wantsEnvelope) {
            if (! $wantsEnvelope($request)) {
                return null;
            }

            $details = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ((array) $messages as $message) {
                    $details[] = ['field' => $field, 'message' => $message];
                }
            }

            return response()->json([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Some fields are invalid.',
                    'details' => $details,
                ],
            ], 422);
        });

        $exceptions->renderable(function (AuthenticationException $e, $request) use ($wantsEnvelope) {
            if (! $wantsEnvelope($request)) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Authentication is required.',
                    'details' => [],
                ],
            ], 401);
        });

        $exceptions->renderable(function (ModelNotFoundException $e, $request) use ($wantsEnvelope) {
            if (! $wantsEnvelope($request)) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'The requested resource does not exist.',
                    'details' => [],
                ],
            ], 404);
        });

        $exceptions->renderable(function (NotFoundHttpException $e, $request) use ($wantsEnvelope) {
            if (! $wantsEnvelope($request)) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'The requested resource does not exist.',
                    'details' => [],
                ],
            ], 404);
        });

        $exceptions->renderable(function (AccessDeniedHttpException $e, $request) use ($wantsEnvelope) {
            if (! $wantsEnvelope($request)) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'You do not have permission to perform this action.',
                    'details' => [],
                ],
            ], 403);
        });

        $exceptions->renderable(function (ThrottleRequestsException $e, $request) use ($wantsEnvelope) {
            if (! $wantsEnvelope($request)) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => 'RATE_LIMITED',
                    'message' => 'Too many requests. Please try again later.',
                    'details' => [],
                ],
            ], 429)->withHeaders($e->getHeaders());
        });

        $exceptions->renderable(function (DomainException $e, $request) use ($wantsEnvelope) {
            if ($wantsEnvelope($request)) {
                return response()->json([
                    'error' => [
                        'code' => 'UNPROCESSABLE_ENTITY',
                        'message' => $e->getMessage(),
                        'details' => [],
                    ],
                ], 422);
            }

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return null;
        });

        /*
        | HTTP exceptions already carry a correct status code (405, 406,
        | 415, ...). Convert them to the envelope while preserving the
        | status, instead of letting the generic Throwable handler below
        | mask them as 500 Server Error.
        */
        $exceptions->renderable(function (HttpExceptionInterface $e, $request) use ($wantsEnvelope) {
            if (! $wantsEnvelope($request)) {
                return null;
            }

            $status = $e->getStatusCode();
            $code = match ($status) {
                400 => 'BAD_REQUEST',
                401 => 'UNAUTHENTICATED',
                403 => 'FORBIDDEN',
                404 => 'NOT_FOUND',
                405 => 'METHOD_NOT_ALLOWED',
                409 => 'CONFLICT',
                422 => 'UNPROCESSABLE_ENTITY',
                429 => 'RATE_LIMITED',
                default => 'SERVER_ERROR',
            };

            return response()->json([
                'error' => [
                    'code' => $code,
                    'message' => $e->getMessage() ?: 'The request could not be completed.',
                    'details' => [],
                ],
            ], $status, $e->getHeaders());
        });

        $exceptions->renderable(function (Throwable $e, $request) use ($wantsEnvelope) {
            if (! $wantsEnvelope($request)
                || $e instanceof ValidationException
                || $e instanceof HttpExceptionInterface) {
                return null;
            }

            // Never leak a stack trace or internal message to the client.
            Log::error('Unhandled API exception.', [
                'exception' => $e::class,
                'request_id' => $request->headers->get('X-Request-ID'),
            ]);

            return response()->json([
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'An unexpected error occurred.',
                    'details' => [],
                ],
            ], 500);
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
