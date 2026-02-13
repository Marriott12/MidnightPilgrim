<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NarrativeReflection Model - PATTERN INSIGHTS
 * 
 * Generated every 5 sessions to provide philosophical observations
 * based on aggregated emotional patterns.
 * 
 * Avoids therapy framing and clinical labels.
 * Maintains philosophical tone.
 */
class NarrativeReflection extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_profile_id',
        'pattern_observations',
        'identified_contradiction',
        'philosophical_question',
        'shown_to_user',
        'shown_at',
    ];

    protected $casts = [
        'pattern_observations' => 'array',
        'shown_to_user' => 'boolean',
        'shown_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user profile for this reflection
     */
    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    /**
     * Mark reflection as shown to user
     */
    public function markAsShown(): void
    {
        $this->update([
            'shown_to_user' => true,
            'shown_at' => now(),
        ]);
    }
}
