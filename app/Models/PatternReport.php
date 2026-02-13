<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PatternReport Model - RECURRING WEAKNESS TRACKING
 * 
 * Identifies and reports patterns across sessions for user accountability.
 * Delivers sharp, technical feedback on recurring flaws.
 */
class PatternReport extends Model
{
    protected $fillable = [
        'user_profile_id',
        'pattern_type',
        'description',
        'evidence',
        'correction_strategy',
        'specific_exercise',
        'acknowledged',
        'acknowledged_at',
    ];

    protected $casts = [
        'evidence' => 'array',
        'acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    /**
     * Mark pattern as acknowledged by user
     */
    public function acknowledge(): void
    {
        $this->acknowledged = true;
        $this->acknowledged_at = now();
        $this->save();
    }

    /**
     * Get formatted evidence for display
     */
    public function getFormattedEvidence(): string
    {
        if (!$this->evidence) {
            return '';
        }

        return collect($this->evidence)
            ->map(fn($item, $index) => ($index + 1) . ". " . $item)
            ->implode("\n");
    }
}
