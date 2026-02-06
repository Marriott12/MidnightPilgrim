<?php

namespace App\Http\Middleware;

use Closure;

/**
 * SetPublicMode - PHASE 0: ACCESS ENFORCEMENT
 * 
 * Marks requests as public-facing context (Waystone routes).
 * Public context can ONLY access content marked 'shareable'.
 * 
 * Used by:
 * - /waystone
 * - /philosophy  
 * - /download
 * - /silence
 * 
 * Services check this flag to filter content appropriately.
 */
class SetPublicMode
{
    /**
     * Mark the application instance as operating in public (Waystone) mode.
     * 
     * PHASE 0: BOUNDARY ENFORCEMENT
     * Services must check this flag and return only shareable content.
     */
    public function handle($request, Closure $next)
    {
        // Set public context flag
        app()->instance('public_mode', true);
        
        // Also set on request for middleware chain
        $request->attributes->set('public_context', true);
        
        return $next($request);
    }
    
    /**
     * Check if current request is in public mode.
     */
    public static function isPublicMode(): bool
    {
        return app()->bound('public_mode') && app('public_mode') === true;
    }
}
