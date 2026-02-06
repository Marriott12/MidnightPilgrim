<?php

namespace App\Services;

use App\Models\CheckIn;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * MentalHealthCompanionService - PHASE 5: PRESENCE WITHOUT TREATMENT
 * 
 * PURPOSE: Two modes of calm presence, never clinical guidance.
 * 
 * MODE A - REFLECTIVE (User-Invoked):
 * - Longer pauses, references existing notes/poems
 * - No interpretations, advice, or diagnoses
 * - Surfaces user's own language back to them
 * - Can say "I don't know" or offer silence
 * 
 * MODE B - CHECK-IN (Brief, Optional):
 * - One gentle question: "How heavy did today feel?"
 * - No follow-up unless user responds
 * - No streaks, penalties, or pressure
 * - Can be skipped without comment
 * 
 * HARD SAFETY BOUNDARIES:
 * - No medical/therapeutic guidance ever
 * - No action suggestions ("try this")
 * - No affirmations or motivation
 * - No external resource recommendations
 * - Always private, stored in companion/
 * - Silence on uncertainty
 * 
 * PHASE 5 WILL NEVER:
 * - Diagnose or assess mental health
 * - Recommend treatments or interventions
 * - Track progress or suggest goals
 * - Gamify engagement or reward streaks
 * - Share mental health data (permanently private)
 * 
 * "Not a therapist, not a coach - a quiet presence."
 */
class MentalHealthCompanionService
{
    protected array $supportPhrases = [
        "That sounds heavy.",
        "I'm here with you.",
        "You're not alone in noticing this.",
    ];

    // Notes on safety: This service is intentionally minimal and non-directive.
    // - It must never diagnose or advise clinically.
    // - It should not call or suggest external services automatically.
    // - If any internal helper fails, silence is preferred.

    /**
     * Store an optional check-in (non-judgmental).
     */
    public function storeCheckIn(string $mood, int $intensity, ?string $note = null): CheckIn
    {
        return CheckIn::create([
            'mood' => Str::lower(trim($mood)),
            'intensity' => max(1, min(5, (int) $intensity)),
            'note' => $note,
        ]);
    }

    /**
     * Return true if repeated high-intensity (>=4) appears over multiple days.
     */
    public function needsHumanSupport(int $days = 3, int $threshold = 4): bool
    {
        $cutoff = Carbon::now()->subDays($days - 1)->startOfDay();

        $countDays = CheckIn::where('intensity', '>=', $threshold)
            ->where('created_at', '>=', $cutoff)
            ->selectRaw('date(created_at) as d')
            ->distinct()
            ->count();

        return $countDays >= $days;
    }

    /**
     * Produce a gentle response to emotional input. Short and non-directive.
     */
    public function respondToInput(string $input): string
    {
        // For backward compatibility: simple witness
        $phrase = $this->supportPhrases[array_rand($this->supportPhrases)];
        if ($this->needsHumanSupport()) {
            $phrase .= ' ' . "You don't have to carry this alone. Talking with someone you trust could help.";
        }
        return $phrase;
    }

    /**
     * Reflective mode: manual, long-form. Uses existing content language when appropriate.
     * Returns a single gentle prompt or null (silence).
     */
    public function reflectiveResponse(string $input): ?string
    {
        try {
            // Attempt to find a reference and mirror it back
            $resolver = app(\App\Services\ReferenceResolver::class);
            $ref = $resolver->resolve($input, false, false);
            if ($ref) {
                $excerpt = str_replace("\n", ' ', $ref['excerpt']);
                return 'You once wrote: "' . $excerpt . '"\nDoes that still feel true?';
            }

            // If no reference, return a quiet invitation
            return "I'm here. You can write, or you can sit.";
        } catch (\Throwable $e) {
            return null; // silence on error
        }
    }

    /**
     * Check-in prompt mode: brief, optional, single question.
     */
    public function checkInPrompt(): string
    {
        return 'How heavy did today feel (1–5)?';
    }

    /**
     * Naive emotional language detector. Returns true if input contains emotional keywords.
     */
    public function detectEmotionalLanguage(string $input): bool
    {
        // Soft detector — only broad states (do not label or score)
        $keywords = ['tired','heavy','lost','overwhelmed'];
        $text = Str::lower($input);
        foreach ($keywords as $k) {
            if (Str::contains($text, $k)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Respond with reference priority: try ReferenceResolver first, otherwise a single witnessing sentence.
     * Returns string or null (silence).
     */
    public function respondToInputWithReferencePriority(string $input): ?string
    {
        try {
            $resolver = app(ReferenceResolver::class);
            $reference = $resolver->resolve($input);

            if ($reference) {
                $slug = $reference['slug'] ?? 'source';
                $excerpt = str_replace("\n", ' ', $reference['excerpt']);
                // One-surface rule: only return a single reference excerpt.
                return 'From "' . $slug . '": "' . $excerpt . '"';
            }

            // No reference — return a single witnessing sentence
            $phrase = $this->supportPhrases[array_rand($this->supportPhrases)];
            if ($this->needsHumanSupport()) {
                $phrase .= ' ' . "You don't have to carry this alone. Talking with someone you trust could help.";
            }

            return $phrase;
        } catch (\Throwable $e) {
            // On any error, prefer silence (null) to an unsafe response.
            return null;
        }
    }
}
