<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        // ...existing Laravel middleware...
        \App\Http\Middleware\InputSanitizer::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // ...existing web middleware...
        ],

        'api' => [
            'throttle:60,1',
            \App\Http\Middleware\ApiRateLimit::class,
            \App\Http\Middleware\InputSanitizer::class,
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // ...existing route middleware...
        'api.rate' => \App\Http\Middleware\ApiRateLimit::class,
        'sanitize' => \App\Http\Middleware\InputSanitizer::class,
    ];
}
