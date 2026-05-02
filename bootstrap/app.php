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
        // Sanctum stateful auth for API routes
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Web stack — order matters:
        // 1. SetCurrentCompany — must run FIRST so the others can read $user->company
        // 2. CheckSystemStatus — kill switch
        // 3. CheckSubscription — trial / subscription gate
        $middleware->web(append: [
            \App\Http\Middleware\SetCurrentCompany::class,
            \App\Http\Middleware\CheckSystemStatus::class,
            \App\Http\Middleware\CheckSubscription::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
                'platform' => \App\Http\Middleware\EnsurePlatformAdmin::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();