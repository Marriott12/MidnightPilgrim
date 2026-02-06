<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CheckIn Model - MENTAL HEALTH ARTIFACT
 * 
 * PHASE 0: PERMANENT PRIVACY COVENANT
 * -----------------------------------
 * Check-ins are ALWAYS private. No visibility attribute exists.
 * This data may NEVER be shared, exported publicly, or made reflective.
 * Storage: storage/companion/ (isolated from public exports)
 * 
 * MIDNIGHT PILGRIM WILL NEVER:
 * - Share check-in data (even anonymized)
 * - Analyze mood trends or patterns
 * - Provide clinical advice or diagnosis
 * - Alert others about check-in status
 * - Gamify mental health (no streaks, scores)
 * - Automatically intervene based on mood
 */
class CheckIn extends Model
{
    protected $table = 'check_ins';

    protected $fillable = [
        'mood',
        'intensity',
        'note',
    ];

    protected $casts = [
        'intensity' => 'integer',
    ];

    /**
     * MENTAL HEALTH ARTIFACTS CANNOT BE SHARED
     * This is a hard boundary. Returns false always.
     */
    public function canBeShared(): bool
    {
        return false;
    }

    /**
     * Check-ins have no visibility attribute.
     * They are implicitly and permanently private.
     */
    public function getVisibilityAttribute(): string
    {
        return 'private';
    }

    /**
     * STORAGE PATH ENFORCEMENT
     * Check-ins belong in storage/companion/ only.
     * This directory is NEVER included in public exports.
     */
    public function getStorageDirectory(): string
    {
        return 'companion';
    }
}
