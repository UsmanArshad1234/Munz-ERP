<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'       => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ]);

        $middleware->api(prepend: [
            ForceJsonResponse::class,
            SecurityHeaders::class,
        ]);

        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // ── 401: Unauthenticated ──────────────────────────────────────────────
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login.',
                ], 401);
            }
        });

        // ── 422: Validation ───────────────────────────────────────────────────
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // ── 403: Authorization ────────────────────────────────────────────────
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. You do not have permission to perform this action.',
                ], 403);
            }
        });

        // ── 404: Model not found (route model binding) ────────────────────────
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $model   = class_basename($e->getModel());
                $message = match ($model) {
                    'Employee'       => 'Employee not found.',
                    'Motorbike'      => 'Motorbike not found.',
                    'Assignment'     => 'Assignment not found.',
                    'Loan'           => 'Loan not found.',
                    'Payroll'        => 'Payroll record not found.',
                    'Fine'           => 'Fine not found.',
                    'Expense'        => 'Expense not found.',
                    'PlatformIncome' => 'Income record not found.',
                    'Maintenance'    => 'Maintenance record not found.',
                    'User'           => 'User not found.',
                    'Setting'        => 'Setting not found.',
                    'AuditLog'       => 'Audit log not found.',
                    default          => "{$model} not found.",
                };
                return response()->json(['success' => false, 'message' => $message], 404);
            }
        });

        // ── 404: Route not found ──────────────────────────────────────────────
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested endpoint does not exist.',
                ], 404);
            }
        });

        // ── 405: Method not allowed ───────────────────────────────────────────
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'HTTP method not allowed for this endpoint.',
                ], 405);
            }
        });

        // ── 429: Too many requests ────────────────────────────────────────────
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many attempts. Please wait before trying again.',
                ], 429);
            }
        });

        // ── Business logic exceptions (thrown by Services) ────────────────────
        // Services throw \Exception with codes 400/422 and user-friendly messages.
        $exceptions->render(function (\Exception $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $code = (is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600)
                    ? $e->getCode()
                    : null;

                // Only handle as business error if it has an HTTP status code
                if ($code !== null) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                    ], $code);
                }

                // Generic server errors — show message in non-production
                $message = config('app.debug')
                    ? $e->getMessage()
                    : 'An unexpected error occurred. Please try again.';

                return response()->json(['success' => false, 'message' => $message], 500);
            }
        });

        // ── Database errors ───────────────────────────────────────────────────
        $exceptions->render(function (\Illuminate\Database\QueryException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $message = config('app.debug')
                    ? 'Database error: ' . $e->getMessage()
                    : 'A database error occurred. Please try again.';

                return response()->json(['success' => false, 'message' => $message], 500);
            }
        });

    })->create();
