<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Poem Model - DISCIPLINE CONTRACT ENFORCEMENT
 * 
 * Tracks poetry submissions for the discipline contract system.
 * Enforces weekly constraints and maintains accountability.
 */
class Poem extends Model
{
    protected $fillable = [
        'user_profile_id',
        'content',
        'line_count',
        'constraint_type',
        'status',
        'submitted_at',
        'published_at',
        'publish_platform',
        'critique',
        'self_assessment',
        'week_number',
        'revision_count',
        'is_monthly_release',
        'is_penalty_poem',
        'archive_path',
        'recording_file_path',
        'public_release_url',
        'revision_notes',
        'reflection_completed',
        'constraint_violations',
    ];

    protected $casts = [
        'critique' => 'array',
        'self_assessment' => 'array',
        'constraint_violations' => 'array',
        'line_count' => 'integer',
        'week_number' => 'integer',
        'revision_count' => 'integer',
        'is_monthly_release' => 'boolean',
        'is_penalty_poem' => 'boolean',
        'reflection_completed' => 'boolean',
        'submitted_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    /**
     * Get all revisions for this poem
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(PoemRevision::class);
    }

    /**
     * Check if poem meets minimum line requirement
     */
    public function meetsLineRequirement(int $requiredLines = 14): bool
    {
        return $this->line_count >= $requiredLines;
    }

    /**
     * Check if poem was submitted within deadline
     */
    public function isOnTime(): bool
    {
        if (!$this->submitted_at) {
            return false;
        }

        // Deadline is Sunday 20:00
        $weekStart = now()->startOfWeek()->addDays(-1); // Start from Sunday
        $deadline = $weekStart->copy()->addWeek()->setTime(20, 0);

        return $this->submitted_at->lte($deadline);
    }

    /**
     * Mark poem as submitted
     */
    public function submit(): void
    {
        $this->status = 'submitted';
        $this->submitted_at = now();
        $this->save();
    }

    /**
     * Mark poem as published
     */
    public function publish(string $platform): void
    {
        $this->status = 'published';
        $this->published_at = now();
        $this->publish_platform = $platform;
        $this->save();
    }

    /**
     * Store critique from Midnight Pilgrim
     */
    public function storeCritique(array $critique): void
    {
        $this->critique = $critique;
        $this->save();
    }

    /**
     * Store self-assessment answers
     */
    public function storeSelfAssessment(array $assessment): void
    {
        $this->self_assessment = $assessment;
        $this->save();
    }

    /**
     * Check if has recording
     */
    public function hasRecording(): bool
    {
        return !empty($this->recording_file_path);
    }

    /**
     * Check if has public release URL
     */
    public function hasPublicRelease(): bool
    {
        return !empty($this->public_release_url);
    }

    /**
     * Get latest revision
     */
    public function latestRevision()
    {
        return $this->revisions()->orderBy('version_number', 'desc')->first();
    }
}
