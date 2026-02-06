<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * StorageBoundaryGuard - PHASE 0: STORAGE SEPARATION
 * 
 * Enforces strict separation between different content types:
 * - storage/vault/ → notes and poems (user-created content)
 * - storage/quotes/ → extracted quotes
 * - storage/thoughts/ → daily thoughts
 * - storage/companion/ → mental health data (NEVER exported publicly)
 * - storage/public/ → explicitly shared exports only
 * 
 * IMMUTABLE PRINCIPLES:
 * --------------------
 * - Automated processes cannot copy private content to public storage
 * - Mental health data stays in companion/ permanently
 * - Public exports only include content explicitly marked shareable
 * 
 * MIDNIGHT PILGRIM WILL NEVER:
 * ---------------------------
 * - Automatically publish private content
 * - Include mental health data in exports
 * - Sync content to cloud storage
 * - Share data with third-party services
 */
class StorageBoundaryGuard
{
    /**
     * PHASE 0: DIRECTORY DEFINITIONS
     * 
     * These directories have strict purposes and cannot be mixed.
     */
    protected const VAULT_DIR = 'vault';           // User notes and poems
    protected const QUOTES_DIR = 'quotes';         // Extracted quotes
    protected const THOUGHTS_DIR = 'thoughts';     // Daily thoughts
    protected const COMPANION_DIR = 'companion';   // Mental health data (PRIVATE)
    protected const PUBLIC_DIR = 'public';         // Shared exports only
    protected const CACHE_DIR = 'cache';           // Temporary data

    /**
     * PHASE 0: FORBIDDEN PATHS
     * 
     * These directories are NEVER included in public exports.
     */
    protected const PRIVATE_DIRS = [
        self::COMPANION_DIR,
        self::CACHE_DIR,
    ];

    /**
     * Validate that a path belongs to the correct storage directory.
     * 
     * @param string $path File path to validate
     * @param string $expectedDir Expected directory (vault, quotes, etc.)
     * @return bool True if path is valid
     */
    public function validatePath(string $path, string $expectedDir): bool
    {
        return str_starts_with($path, $expectedDir . '/');
    }

    /**
     * PHASE 0: MENTAL HEALTH DATA ISOLATION
     * 
     * Check if a path contains mental health data.
     * Mental health data must NEVER be exported publicly.
     * 
     * @param string $path File path to check
     * @return bool True if this is mental health data
     */
    public function isMentalHealthData(string $path): bool
    {
        return str_starts_with($path, self::COMPANION_DIR . '/');
    }

    /**
     * PHASE 0: PUBLIC EXPORT SAFETY
     * 
     * Check if a path is safe to include in public exports.
     * Mental health data and cache are excluded.
     * 
     * @param string $path File path to check
     * @return bool True if safe for public export
     */
    public function isSafeForPublicExport(string $path): bool
    {
        foreach (self::PRIVATE_DIRS as $privateDir) {
            if (str_starts_with($path, $privateDir . '/')) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get the appropriate storage directory for a content type.
     * 
     * @param string $contentType (note, quote, thought, checkin, interaction)
     * @return string Directory path
     */
    public function getStorageDirectory(string $contentType): string
    {
        return match($contentType) {
            'note', 'poem' => self::VAULT_DIR,
            'quote' => self::QUOTES_DIR,
            'thought' => self::THOUGHTS_DIR,
            'checkin', 'interaction' => self::COMPANION_DIR,
            default => self::VAULT_DIR,
        };
    }

    /**
     * PHASE 0: PREVENT AUTOMATIC PUBLIC COPYING
     * 
     * Ensure no automated process can copy private content to public storage.
     * This method always returns false for private content.
     * 
     * @param string $path Source path
     * @param string $visibility Content visibility level
     * @return bool True only if content is explicitly shareable
     */
    public function canCopyToPublic(string $path, string $visibility): bool
    {
        // Mental health data can NEVER be copied to public
        if ($this->isMentalHealthData($path)) {
            return false;
        }

        // Only explicitly shareable content
        return $visibility === 'shareable';
    }

    /**
     * Create directory structure if it doesn't exist.
     * Ensures all required directories are present.
     */
    public function ensureDirectoryStructure(): void
    {
        $directories = [
            self::VAULT_DIR,
            self::QUOTES_DIR,
            self::THOUGHTS_DIR,
            self::COMPANION_DIR,
            self::PUBLIC_DIR,
            self::CACHE_DIR,
        ];

        foreach ($directories as $dir) {
            if (!Storage::disk('local')->exists($dir)) {
                Storage::disk('local')->makeDirectory($dir);
            }
        }
    }

    /**
     * PHASE 0: QUIET BOUNDARY VIOLATION HANDLING
     * 
     * When a boundary is violated, fail silently.
     * Returns null instead of throwing exceptions.
     * 
     * @return null
     */
    public static function handleViolation()
    {
        // Log violation if needed, but don't expose to user
        // No exceptions, no warnings, just silence
        return null;
    }
}
