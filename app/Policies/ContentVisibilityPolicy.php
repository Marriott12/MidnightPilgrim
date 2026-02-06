<?php

namespace App\Policies;

use App\Models\Note;
use Illuminate\Support\Facades\Route;

/**
 * ContentVisibilityPolicy - PHASE 0: ACCESS ENFORCEMENT
 * 
 * Enforces visibility boundaries across all content types:
 * - Private: Internal access only, never surfaced automatically
 * - Reflective: Internal only, may be referenced by assistant
 * - Shareable: Accessible via public routes
 * 
 * BOUNDARY VIOLATIONS FAIL QUIETLY
 * No exceptions, no stack traces. Just silence (null or empty).
 */
class ContentVisibilityPolicy
{
    /**
     * Determine if the current context is public-facing.
     * Public routes are isolated and can only access shareable content.
     */
    protected function isPublicContext(): bool
    {
        $publicRoutes = [
            'waystone.*',
            'philosophy',
            'download',
            'silence',
        ];

        $currentRoute = Route::currentRouteName();
        
        foreach ($publicRoutes as $pattern) {
            if (fnmatch($pattern, $currentRoute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * PHASE 0: VISIBILITY ENFORCEMENT
     * 
     * Determines if content can be accessed in the current context.
     * Returns false quietly if boundary violated.
     */
    public function view($user, $content): bool
    {
        // Mental health artifacts are never accessible via policy checks
        if (method_exists($content, 'canBeShared') && !$content->canBeShared()) {
            return false;
        }

        // Public context: only shareable content
        if ($this->isPublicContext()) {
            return isset($content->visibility) && $content->visibility === 'shareable';
        }

        // Internal context: all content accessible
        return true;
    }

    /**
     * PHASE 0: SHARING ENFORCEMENT
     * 
     * Only allows sharing for non-mental-health content.
     * Mental health artifacts (CheckIn, Interaction) cannot be shared.
     */
    public function share($user, $content): bool
    {
        // Check if this is a mental health artifact
        if (method_exists($content, 'canBeShared')) {
            return $content->canBeShared();
        }

        // If no canBeShared method, assume not shareable
        return false;
    }

    /**
     * PHASE 0: VISIBILITY CHANGE ENFORCEMENT
     * 
     * Prevents automatic visibility changes.
     * Mental health artifacts cannot have visibility changed.
     */
    public function changeVisibility($user, $content): bool
    {
        // Mental health artifacts have no visibility changes
        if (method_exists($content, 'canBeShared') && !$content->canBeShared()) {
            return false;
        }

        // Other content can have visibility changed by user
        return true;
    }

    /**
     * QUIET FAILURE
     * 
     * When a boundary is violated, return empty result.
     * No exceptions, no warnings, no stack traces.
     * Just silence.
     */
    public static function failQuietly()
    {
        return null;
    }
}
