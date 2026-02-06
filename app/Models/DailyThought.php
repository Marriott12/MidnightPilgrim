<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * DailyThought Model - Immutable Foundation
 * 
 * PHASE 0: VISIBILITY COVENANT
 * ---------------------------
 * All daily thoughts have visibility: private (default) | reflective | shareable
 * Daily thoughts are personal reflections, default to private.
 * Sharing requires explicit, deliberate user action.
 * 
 * MIDNIGHT PILGRIM WILL NEVER:
 * - Generate thoughts automatically (no scheduled tasks)
 * - Analyze thought sentiment or mood trends
 * - Notify users to create thoughts (no reminders)
 * - Track thought creation streaks or consistency
 */
class DailyThought extends Model
{
    protected $table = 'daily_thoughts';

    protected $fillable = [
        'title',
        'slug',
        'body',
        'mood',
        'path',
        'date_generated',
        'visibility',
    ];

    protected $casts = [
        'date_generated' => 'datetime',
    ];

    /**
     * IMMUTABLE DEFAULTS
     * Daily thoughts are private by nature.
     */
    protected $attributes = [
        'visibility' => 'private',
    ];

    /**
     * VISIBILITY ENFORCEMENT
     */
    public function canBeShared(): bool
    {
        return true;
    }

    public function scopeShareableOnly($query)
    {
        return $query->where('visibility', 'shareable');
    }

    /**
     * STORAGE PATH ENFORCEMENT
     * Thoughts belong in storage/thoughts/ only.
     */
    public function getStorageDirectory(): string
    {
        return 'thoughts';
    }
}
