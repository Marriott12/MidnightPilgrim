<?php

namespace App\Policies;

use App\Models\DailyThought;
use App\Models\Interaction;
use Carbon\Carbon;
use App\Services\RhythmResolver;

class SilencePolicy
{
    // Maximum daily thoughts allowed per day
    protected int $maxDailyThoughts = 1;

    // Cooldown in seconds between assistant responses
    protected int $assistantCooldownSeconds = 60;
    // Maximum surfaced reflections per day
    protected int $maxReflectionsPerDay = 1;
    // Days window to avoid resurfacing same reference
    protected int $resurfaceWindowDays = 30;

    // Rhythm-aware limits
    protected array $rhythmLimits = [
        'pulse' => [
            'max_surfaces' => 1, // max surfaced references per interaction
            'max_sentences' => 1, // if surfacing, keep to one sentence
            'easy_exit' => true,
        ],
        'vigil' => [
            'max_surfaces' => 2,
            'max_sentences' => 3,
            'allow_poems' => true,
            'temporal_weaving' => true,
        ],
    ];

    /**
     * Allow generating a daily thought?
     */
    public function allowDailyThought(): bool
    {
        $count = DailyThought::whereDate('date_generated', Carbon::today())->count();
        return $count < $this->maxDailyThoughts;
    }

    /**
     * Allow the assistant to emit a response now?
     */
    public function allowAssistantResponse(): bool
    {
        $last = Interaction::whereNotNull('response_text')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $last) {
            return true;
        }

        $elapsed = Carbon::now()->diffInSeconds(Carbon::parse($last->created_at));

        return $elapsed >= $this->assistantCooldownSeconds;
    }

    /**
     * Allow surfacing a specific reference (slug) according to silence rules.
     */
    public function allowSurfaceReference(string $slug): bool
    {
        // rhythm suggestion should be consulted by callers; do not auto-change user consent
        $rhythm = app(RhythmResolver::class)->determine();
        $limits = $this->getRhythmLimits($rhythm);

        // Respect cooldown for assistant responses
        if (! $this->allowAssistantResponse()) {
            return false;
        }

        // Limit reflections per day
        $todayCount = Interaction::whereNotNull('response_text')
            ->whereDate('created_at', Carbon::today())
            ->count();
        if ($todayCount >= $this->maxReflectionsPerDay) {
            return false;
        }

        // Prevent resurfacing the same slug within the window
        $cutoff = Carbon::now()->subDays($this->resurfaceWindowDays)->startOfDay();
        $found = Interaction::where('response_text', 'like', '%From "' . $slug . '"%')
            ->where('created_at', '>=', $cutoff)
            ->exists();

        if ($found) {
            return false;
        }

        // Respect rhythm max surfaces (basic enforcement)
        if (isset($limits['max_surfaces']) && $limits['max_surfaces'] <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Allow temporal phrase for today (only once per day overall).
     */
    public function allowTemporalToday(): bool
    {
        $todayCount = Interaction::whereNotNull('response_text')
            ->whereDate('created_at', Carbon::today())
            ->where('response_text', 'like', '%You wrote this%')
            ->count();

        return $todayCount < 1;
    }

    /**
     * Prevent repeating temporal phrase for the same slug within a window.
     */
    public function allowTemporalForSlug(string $slug): bool
    {
        $cutoff = Carbon::now()->subDays(30)->startOfDay();
        $found = Interaction::where('response_text', 'like', '%"' . $slug . '"%')
            ->where('created_at', '>=', $cutoff)
            ->exists();

        return ! $found && $this->allowTemporalToday();
    }

    /**
     * Recommend silence for a given intent/mode.
     * Returns true when silence is recommended.
     */
    public function recommendSilence(string $mode): bool
    {
        if ($mode === 'listen') {
            return true; // listen mode is explicitly silent
        }

        $rhythm = app(RhythmResolver::class)->determine();
        // In pulse, prefer silence unless explicit quick check
        if ($rhythm === 'pulse' && ! $this->allowAssistantResponse()) {
            return true;
        }

        return ! $this->allowAssistantResponse();
    }

    /**
     * Get rhythm-specific limits
     */
    public function getRhythmLimits(string $rhythm): array
    {
        return $this->rhythmLimits[$rhythm] ?? [];
    }

    public function isPulse(?string $input = null): bool
    {
        return app(RhythmResolver::class)->isPulse($input);
    }
}
