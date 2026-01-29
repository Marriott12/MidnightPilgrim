<?php

namespace App\Http\Middleware;

use Closure;

class SetPublicMode
{
    /**
     * Mark the application instance as operating in public (Waystone) mode.
     */
    public function handle($request, Closure $next)
    {
        // set a simple container binding so other code can detect public mode
        app()->instance('public_mode', true);
        return $next($request);
    }
}
