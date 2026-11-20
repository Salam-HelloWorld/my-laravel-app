<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // API-only app: routes live in routes/api.php and are served from the root (no "/api" prefix).
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render (and most hosts) put a proxy in front of the app. Trust it so HTTPS URLs are generated correctly.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always answer with JSON, even for errors.
        $exceptions->shouldRenderJsonWhen(fn () => true);
    })->create();
