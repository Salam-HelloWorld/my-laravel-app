<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class MetaController extends Controller
{
    /** Welcome message. */
    public function root(): JsonResponse
    {
        return response()->json([
            'message' => config('app.welcome_message'),
            'docs' => '/docs',
        ]);
    }

    /** Health check (use this as the Render "Health Check Path"). */
    public function health(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }

    /**
     * Shows which config the running server picked up.
     * Secrets are never returned, only whether they are set.
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_version' => config('app.version'),
            'welcome_message' => config('app.welcome_message'),
            'api_secret_configured' => config('app.api_secret') !== '' && config('app.api_secret') !== null,
            'app_key_configured' => ! empty(config('app.key')),
        ]);
    }
}
