<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * UserProfile Model - EMOTIONAL PATTERN TRACKING
 * 
 * Stores long-term emotional metrics and patterns for anonymous users
 * identified by soft fingerprinting (IP hash + user agent hash).
 * 
 * Privacy-conscious:
 * - No personally identifiable information stored
 * - Uses hashed fingerprints only
 * - Can be deleted on user request
 */
class UserProfile extends Model
{
    protected $fillable = [
        'fingerprint',
        'emotional_baseline',
        'volatility_score',
        'absolutist_language_frequency',
        'self_criticism_index',
        'recurring_topics',
        'time_of_day_emotional_drift',
        'session_depth_score',
        'preferred_mode',
        'timezone',
        'declared_platform',
        'platform_locked',
        'platform_declared_at',
        'total_sessions',
        'sessions_since_reflection',
        'last_session_at',
    ];

    protected $casts = [
        'recurring_topics' => 'array',
        'time_of_day_emotional_drift' => 'array',
        'emotional_baseline' => 'float',
        'volatility_score' => 'float',
        'self_criticism_index' => 'float',
        'session_depth_score' => 'float',
        'total_sessions' => 'integer',
        'sessions_since_reflection' => 'integer',
        'absolutist_language_frequency' => 'integer',
        'platform_locked' => 'boolean',
        'platform_declared_at' => 'datetime',
        'last_session_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all sessions for this profile
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Get all emotional snapshots for this profile
     */
    public function emotionalSnapshots(): HasMany
    {
        return $this->hasMany(EmotionalSnapshot::class);
    }

    /**
     * Get all narrative reflections for this profile
     */
    public function narrativeReflections(): HasMany
    {
        return $this->hasMany(NarrativeReflection::class);
    }

    /**
     * Get discipline contracts for this profile
     */
    public function disciplineContracts(): HasMany
    {
        return $this->hasMany(DisciplineContract::class);
    }

    /**
     * Get poems for this profile
     */
    public function poems(): HasMany
    {
        return $this->hasMany(Poem::class);
    }

    /**
     * Get pattern reports for this profile
     */
    public function patternReports(): HasMany
    {
        return $this->hasMany(PatternReport::class);
    }

    /**
     * Get active discipline contract
     */
    public function activeDisciplineContract(): ?DisciplineContract
    {
        return $this->disciplineContracts()
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    }

    /**
     * Check if user needs a narrative reflection (every 5 sessions)
     */
    public function needsReflection(): bool
    {
        return $this->sessions_since_reflection >= 5;
    }

    /**
     * Increment session counter
     */
    public function incrementSessionCounter(): void
    {
        $this->increment('total_sessions');
        $this->increment('sessions_since_reflection');
        $this->update(['last_session_at' => now()]);
    }

    /**
     * Reset reflection counter after generating reflection
     */
    public function resetReflectionCounter(): void
    {
        $this->update(['sessions_since_reflection' => 0]);
    }

    /**
     * Check if platform is declared
     */
    public function hasDeclaredPlatform(): bool
    {
        return !empty($this->declared_platform) && $this->platform_locked;
    }

    /**
     * Declare platform (one-time, irreversible)
     */
    public function declarePlatform(string $platform, string $timezone): void
    {
        if ($this->platform_locked) {
            throw new \RuntimeException('Platform already declared and locked. Cannot change.');
        }

        $this->update([
            'declared_platform' => $platform,
            'timezone' => $timezone,
            'platform_locked' => true,
            'platform_declared_at' => now(),
        ]);
    }
}
