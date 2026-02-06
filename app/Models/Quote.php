<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Quote Model - Immutable Foundation
 * 
 * PHASE 0: VISIBILITY COVENANT
 * ---------------------------
 * All quotes have visibility: private (default) | reflective | shareable
 * Quotes inherit visibility rules but can be elevated independently.
 * User must explicitly choose to share.
 * 
 * MIDNIGHT PILGRIM WILL NEVER:
 * - Automatically select "best" quotes
 * - Rank or score quotes by engagement
 * - Recommend quotes based on mood analysis
 * - Share quotes without explicit consent
 */
class Quote extends Model
{
    protected $fillable = [
        'slug',
        'body',
        'source_note_id',
        'path',
        'confidence',
        'visibility',
    ];

    /**
     * IMMUTABLE DEFAULTS
     * All new quotes are private by default.
     */
    protected $attributes = [
        'visibility' => 'private',
        'confidence' => 'manual',
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
     * Quotes belong in storage/quotes/ only.
     */
    public function getStorageDirectory(): string
    {
        return 'quotes';
    }
}
