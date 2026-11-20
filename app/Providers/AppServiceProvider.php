<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // API docs: UI at /docs, raw OpenAPI JSON at /openapi.json (same URLs as the FastAPI & NestJS templates).
        Scramble::configure()
            ->expose(ui: 'docs', document: 'openapi.json')
            ->routes(fn (Route $route) => in_array('api', $route->middleware(), true));
    }

    public function boot(): void
    {
        //
    }
}
