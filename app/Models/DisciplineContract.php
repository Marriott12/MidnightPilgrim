<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * DisciplineContract Model - POETRY ENFORCEMENT
 * 
 * Tracks the binding contract for weekly poetry submissions.
 * Enforces deadlines, penalties, and monthly releases.
 */
class DisciplineContract extends Model
{
    protected $fillable = [
        'user_profile_id',
        'start_date',
        'end_date',
        'status',
        'total_weeks',
        'poems_submitted',
        'poems_missed',
        'monthly_releases',
        'monthly_releases_missed',
        'missed_weeks',
        'last_submission_at',
        'user_timezone',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_weeks' => 'integer',
        'poems_submitted' => 'integer',
        'poems_missed' => 'integer',
        'monthly_releases' => 'integer',
        'monthly_releases_missed' => 'integer',
        'missed_weeks' => 'array',
        'last_submission_at' => 'datetime',
    ];

    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    public function poems(): HasMany
    {
        return $this->hasMany(Poem::class, 'user_profile_id', 'user_profile_id');
    }

    public function constraintCycles(): HasMany
    {
        return $this->hasMany(ConstraintCycle::class);
    }

    public function complianceLogs(): HasMany
    {
        return $this->hasMany(ComplianceLog::class);
    }

    /**
     * Get current week number in contract
     */
    public function getCurrentWeekNumber(): int
    {
        $now = now($this->user_timezone ?? 'UTC');
        
        if ($now->lt($this->start_date)) {
            return 0; // Contract hasn't started
        }
        
        if ($now->gt($this->end_date)) {
            return $this->total_weeks + 1; // Contract ended
        }

        return $this->start_date->diffInWeeks($now) + 1;
    }

    /**
     * Check if contract is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && now()->gte($this->start_date) 
            && now()->lte($this->end_date);
    }

    /**
     * Get penalty multiplier for minimum lines
     * Two misses in a month = 28 lines instead of 14
     */
    public function getMinimumLines(): int
    {
        $baseLines = 14;
        
        // Check misses in current month
        $currentMonth = now()->month;
        $missesThisMonth = collect($this->missed_weeks ?? [])
            ->filter(function ($weekNumber) use ($currentMonth) {
                $weekDate = $this->start_date->copy()->addWeeks($weekNumber - 1);
                return $weekDate->month === $currentMonth;
            })
            ->count();

        return $missesThisMonth >= 2 ? 28 : $baseLines;
    }

    /**
     * Record a missed submission
     */
    public function recordMiss(int $weekNumber): void
    {
        $this->poems_missed++;
        $missedWeeks = $this->missed_weeks ?? [];
        $missedWeeks[] = $weekNumber;
        $this->missed_weeks = $missedWeeks;
        $this->save();
    }

    /**
     * Record a successful submission
     */
    public function recordSubmission(): void
    {
        $this->poems_submitted++;
        $this->last_submission_at = now();
        $this->save();
    }

    /**
     * Record a monthly release
     */
    public function recordMonthlyRelease(): void
    {
        $this->monthly_releases++;
        $this->save();
    }

    /**
     * Record a missed monthly release
     */
    public function recordMissedMonthlyRelease(): void
    {
        $this->monthly_releases_missed++;
        $this->save();
    }

    /**
     * Check if monthly release is due this month
     */
    public function isMonthlyReleaseDue(): bool
    {
        $currentMonth = now()->month;
        $lastDayOfMonth = now()->endOfMonth()->day;
        
        // Check if we already released this month
        $releasedThisMonth = $this->poems()
            ->where('is_monthly_release', true)
            ->whereMonth('published_at', $currentMonth)
            ->exists();

        return !$releasedThisMonth && now()->day >= $lastDayOfMonth - 2; // Within 2 days of month end
    }
}
