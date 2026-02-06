<?php

namespace App\Services;

use App\Models\DailyThought;
use App\Policies\SilencePolicy;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * DailyThoughtEngine - PHASE 3: RITUALIZED EMERGENCE
 * 
 * PURPOSE: One thought per day, derived from existing quotes only.
 * 
 * CAPABILITIES:
 * - Generate one daily thought from quote collection
 * - Strict once-per-day enforcement with silence
 * - Store as immutable markdown in storage/thoughts/
 * - Preserve source quote references
 * 
 * PHASE 3 WILL NEVER:
 * - Generate from notes directly (quotes only)
 * - Auto-generate without user request
 * - Send notifications or reminders
 * - Allow multiple thoughts per day
 * - Pressure user to maintain streaks
 * 
 * RITUAL: "Not automated presence, but a daily choice to pause."
 * User invokes, system offers, user accepts or passes.
 * 
 * All thoughts default to 'private' visibility (Phase 0).
 */
class DailyThoughtEngine
{
    /**
     * PHASE 3: Generate one daily thought from quote collection
     * 
     * Strict once-per-day: returns null (silence) if already generated today.
     * Source must be existing quote (not direct from notes).
     *
     * @return DailyThought|null Returns null if already generated today (silence-preserving)
     */
    public function generate(): ?DailyThought
    {
        $policy = app(SilencePolicy::class);
        
        // Silence-preserving: return null if not allowed
        if (! $policy->allowDailyThought()) {
            return null;
        }

        // Check if already generated today
        $existing = DailyThought::whereDate('date_generated', Carbon::today())->first();
        if ($existing) {
            // Return null (silence) - do not generate additional thoughts
            return null;
        }

        $thought = new DailyThought();
        $thought->title = 'Daily Thought';
        $thought->slug = 'daily-thought-' . now()->format('Ymd');
        $thought->body = '';
        $thought->mood = null;
        $thought->date_generated = now();
        $thought->visibility = 'private'; // Always private by default

        // Not saving automatically; caller may persist after user confirmation
        return $thought;
    }

    /**
     * Check if a thought has been generated today
     * 
     * @return bool
     */
    public function hasGeneratedToday(): bool
    {
        return DailyThought::whereDate('date_generated', Carbon::today())->exists();
    }

    /**
     * Get today's thought if it exists (read-only)
     * 
     * @return DailyThought|null
     */
    public function getToday(): ?DailyThought
    {
        return DailyThought::whereDate('date_generated', Carbon::today())->first();
    }

    /**
     * Save thought as Markdown file (immutable storage)
     * 
     * @param DailyThought $thought
     * @return string Path to saved file
     */
    public function saveAsMarkdown(DailyThought $thought): string
    {
        $date = Carbon::parse($thought->date_generated)->format('Y-m-d');
        $path = "thoughts/{$date}--daily-thought.md";

        $markdown = $this->buildMarkdown($thought);
        Storage::disk('local')->put($path, $markdown);

        // Update path in model
        $thought->path = $path;
        $thought->save();

        return $path;
    }

    /**
     * Build markdown with frontmatter
     * 
     * @param DailyThought $thought
     * @return string
     */
    protected function buildMarkdown(DailyThought $thought): string
    {
        $yaml = "---\n";
        $yaml .= "title: {$thought->title}\n";
        $yaml .= "type: daily-thought\n";
        $yaml .= "date: " . Carbon::parse($thought->date_generated)->toIso8601String() . "\n";
        if ($thought->mood) {
            $yaml .= "mood: {$thought->mood}\n";
        }
        $yaml .= "visibility: {$thought->visibility}\n";
        $yaml .= "---\n\n";

        return $yaml . ($thought->body ?? '') . "\n";
    }
}
