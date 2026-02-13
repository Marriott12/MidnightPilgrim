<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ComplianceLog Model - WEEKLY COMPLIANCE TRACKING
 * 
 * Single source of truth for contract compliance.
 * Each week gets one log entry with all requirements tracked.
 */
class ComplianceLog extends Model
{
    protected $fillable = [
        'discipline_contract_id',
        'user_profile_id',
        'week_number',
        'on_time',
        'revision_done',
        'reflection_done',
        'constraint_followed',
        'penalty_triggered',
        'status',
        'notes',
        'deadline_at',
        'submitted_at',
    ];

    protected $casts = [
        'on_time' => 'boolean',
        'revision_done' => 'boolean',
        'reflection_done' => 'boolean',
        'constraint_followed' => 'boolean',
        'penalty_triggered' => 'boolean',
        'deadline_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function disciplineContract(): BelongsTo
    {
        return $this->belongsTo(DisciplineContract::class);
    }

    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    /**
     * Mark as completed successfully
     */
    public function markCompleted(): void
    {
        $this->status = 'completed';
        $this->save();
    }

    /**
     * Mark as missed
     */
    public function markMissed(): void
    {
        $this->status = 'missed';
        $this->penalty_triggered = true;
        $this->save();
    }

    /**
     * Check if in recovery window
     */
    public function isInRecoveryWindow(): bool
    {
        if (!$this->deadline_at) {
            return false;
        }

        $recoveryEnd = $this->deadline_at->copy()->addHours(24);
        return now()->between($this->deadline_at, $recoveryEnd);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'completed' => 'green',
            'missed' => 'red',
            'in_recovery' => 'orange',
            'pending' => 'gray',
            default => 'gray',
        };
    }
}
