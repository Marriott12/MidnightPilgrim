<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Interaction Model - MENTAL HEALTH ARTIFACT
 * 
 * PHASE 0: PERMANENT PRIVACY COVENANT
 * -----------------------------------
 * Interactions with the assistant are ALWAYS private.
 * No visibility attribute exists. Cannot be shared, exported, or surfaced.
 * Storage: storage/companion/ (isolated from public exports)
 * 
 * MIDNIGHT PILGRIM WILL NEVER:
 * - Share conversation history
 * - Train AI models on user interactions
 * - Analyze conversation sentiment
 * - Make interactions searchable publicly
 * - Use interactions for recommendations
 */
class Interaction extends Model
{
    protected $table = 'interactions';

    protected $fillable = [
        'input_text',
        'response_text',
        'mode',
    ];

    protected $casts = [
        // Minimal casts - no metadata tracking
    ];

    /**
     * MENTAL HEALTH ARTIFACTS CANNOT BE SHARED
     */
    public function canBeShared(): bool
    {
        return false;
    }

    /**
     * Interactions are implicitly and permanently private.
     */
    public function getVisibilityAttribute(): string
    {
        return 'private';
    }

    /**
     * STORAGE PATH ENFORCEMENT
     * Interactions belong in storage/companion/ only.
     */
    public function getStorageDirectory(): string
    {
        return 'companion';
    }
}
