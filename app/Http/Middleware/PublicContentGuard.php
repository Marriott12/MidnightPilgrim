<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * PublicContentGuard - PHASE 0: ACCESS ENFORCEMENT
 * 
 * Ensures public-facing routes can ONLY access shareable content.
 * Prevents private or reflective content from leaking to public views.
 * 
 * FAILS QUIETLY: Boundary violations return empty results, not errors.
 */
class PublicContentGuard
{
    /**
     * Handle an incoming request.
     * 
     * Sets a flag indicating this is a public context.
     * Services and controllers can check this flag to filter content.
     */
    public function handle(Request $request, Closure $next)
    {
        // Mark this request as public context
        $request->attributes->set('public_context', true);
        
        return $next($request);
    }

    /**
     * Check if current request is in public context.
     */
    public static function isPublicContext(Request $request): bool
    {
        return $request->attributes->get('public_context', false);
    }
}
