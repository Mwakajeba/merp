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
                    'company.scope' => \App\Http\Middleware\CompanyScopeMiddleware::class,
                    'role' => \App\Http\Middleware\CheckRole::class,
                    'permission' => \App\Http\Middleware\CheckPermission::class,
                    'apply.settings' => \App\Http\Middleware\ApplySystemSettings::class,
                    'set.locale' => \App\Http\Middleware\SetLocale::class,
                    'check.inventory.cost.method' => \App\Http\Middleware\CheckInventoryCostMethod::class,
                    'require.branch' => \App\Http\Middleware\RequireBranchSelection::class,
                    'throttle.login' => \App\Http\Middleware\ThrottleLoginAttempts::class,
                    'throttle.api' => \App\Http\Middleware\ThrottleApiRequests::class,
                    'throttle.global' => \App\Http\Middleware\ThrottleGlobalRequests::class,
                    'throttle' => \App\Http\Middleware\ThrottleByConfig::class,
                    'check.menu.access' => \App\Http\Middleware\CheckMenuAccess::class,
                ]);
                
                // Trust proxies first (needed for correct HTTPS detection behind load balancers)
                // This uses Laravel's built-in trustProxies method
                $middleware->trustProxies(
                    at: '*',
                    headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                             \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                             \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                             \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
                             \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
                );
                
                // Force HTTPS redirect (must be early in the stack, after TrustProxies)
                $middleware->prepend(\App\Http\Middleware\ForceHttps::class);
                // CSP nonce (must run before views so inline scripts can use it)
                $middleware->prepend(\App\Http\Middleware\CspNonce::class);
                
                // Apply security headers (HSTS, CSP, etc.)
                $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
                
                // Apply global rate limiting to all requests (DDoS protection)
                $middleware->append(\App\Http\Middleware\ThrottleGlobalRequests::class);
                
                // Apply system settings globally
                $middleware->append(\App\Http\Middleware\ApplySystemSettings::class);
                
                // Set locale globally
                $middleware->append(\App\Http\Middleware\SetLocale::class);
                
                // Set default location globally for authenticated users
                $middleware->append(\App\Http\Middleware\SetDefaultLocation::class);
                
                // Check subscription status for authenticated users
                $middleware->append(\App\Http\Middleware\CheckSubscriptionStatus::class);
                
                // Check password expiration for authenticated users
                $middleware->append(\App\Http\Middleware\CheckPasswordExpiration::class);
                
                // Check menu access for authenticated users (prevents URL bypass)
                $middleware->append(\App\Http\Middleware\CheckMenuAccess::class);
            })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Benign browser / tooling 404s (Chrome DevTools, missing source maps, etc.)
        $exceptions->dontReport([
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        ]);

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            $loginUrl = route('login', ['expired' => 1]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('app.session_expired'),
                    'redirect' => $loginUrl,
                ], 419);
            }

            return redirect($loginUrl);
        });

        // Handle exceptions gracefully
        $exceptions->render(function (Throwable $e, $request) {
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                return null;
            }

            // Do not log routine 404s (source maps, .well-known probes, unknown routes)
            if (! $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                \Log::error('Exception occurred', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // For AJAX requests, return JSON with the correct HTTP status
            if ($request->expectsJson() || $request->ajax()) {
                $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                    ? $e->getStatusCode()
                    : 500;

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null,
                ], $status);
            }

            // For web requests, let Laravel handle it normally
            return null;
        });
    })->create();
