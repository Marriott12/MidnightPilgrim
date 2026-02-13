<?php

namespace App\Services;

use App\Models\UserProfile;
use App\Models\Session;
use App\Models\PatternReport;
use App\Models\Poem;
use Illuminate\Support\Collection;

/**
 * PatternTrackingService - RECURRING WEAKNESS DETECTION
 * 
 * Tracks patterns across sessions and poems to identify recurring flaws:
 * - Abstraction drift
 * - Repetitive themes
 * - Rhythm instability
 * - Sentimental excess
 * - Intellectual posturing
 * 
 * Delivers sharp, technical reports with evidence and correction strategies.
 */
class PatternTrackingService
{
    private const PATTERN_THRESHOLD = 3; // Number of occurrences before flagging

    /**
     * Analyze sessions and poems for patterns
     */
    public function analyzePatterns(UserProfile $profile): Collection
    {
        $patterns = collect();

        // Analyze conversation patterns
        $patterns = $patterns->merge($this->analyzeConversationPatterns($profile));

        // Analyze poetry patterns
        $patterns = $patterns->merge($this->analyzePoetryPatterns($profile));

        return $patterns;
    }

    /**
     * Analyze conversation patterns from recent sessions
     */
    private function analyzeConversationPatterns(UserProfile $profile): Collection
    {
        $patterns = collect();
        $recentSessions = $profile->sessions()
            ->where('status', 'closed')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($recentSessions->isEmpty()) {
            return $patterns;
        }

        // Check for abstraction drift
        $abstractionCount = $recentSessions->sum('abstraction_count');
        if ($abstractionCount >= self::PATTERN_THRESHOLD) {
            $patterns->push($this->createAbstractionDriftReport($profile, $recentSessions));
        }

        // Check for avoidance
        $avoidanceCount = $recentSessions->sum('avoidance_detected_count');
        if ($avoidanceCount >= self::PATTERN_THRESHOLD) {
            $patterns->push($this->createAvoidanceReport($profile, $recentSessions));
        }

        // Check for grandiosity
        $grandiosityCount = $recentSessions->where('grandiosity_detected', true)->count();
        if ($grandiosityCount >= self::PATTERN_THRESHOLD) {
            $patterns->push($this->createGrandiosityReport($profile, $recentSessions));
        }

        // Check for self-mythologizing
        $mythologizingCount = $recentSessions->where('self_mythologizing_detected', true)->count();
        if ($mythologizingCount >= self::PATTERN_THRESHOLD) {
            $patterns->push($this->createMythologizingReport($profile, $recentSessions));
        }

        return $patterns;
    }

    /**
     * Analyze poetry patterns
     */
    private function analyzePoetryPatterns(UserProfile $profile): Collection
    {
        $patterns = collect();
        $recentPoems = $profile->poems()
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($recentPoems->isEmpty()) {
            return $patterns;
        }

        // Check for repetitive themes (analyze poem content)
        $themes = $this->extractThemes($recentPoems);
        if ($this->hasRepetitiveThemes($themes)) {
            $patterns->push($this->createRepetitiveThemesReport($profile, $recentPoems, $themes));
        }

        // Check for rhythm instability (based on critique data)
        if ($this->hasRhythmInstability($recentPoems)) {
            $patterns->push($this->createRhythmInstabilityReport($profile, $recentPoems));
        }

        // Check for sentimental excess
        if ($this->hasSentimentalExcess($recentPoems)) {
            $patterns->push($this->createSentimentalExcessReport($profile, $recentPoems));
        }

        return $patterns;
    }

    /**
     * Create abstraction drift report
     */
    private function createAbstractionDriftReport(UserProfile $profile, Collection $sessions): PatternReport
    {
        $evidence = $sessions
            ->where('abstraction_count', '>', 0)
            ->map(fn($s) => "Session " . $s->id . ": {$s->abstraction_count} abstract statements")
            ->values()
            ->toArray();

        return PatternReport::create([
            'user_profile_id' => $profile->id,
            'pattern_type' => 'abstraction_drift',
            'description' => 'Recurring tendency to retreat into abstraction instead of concrete specificity.',
            'evidence' => $evidence,
            'correction_strategy' => 'Force specificity. Every claim must be supported by a concrete example. No generalizations without evidence.',
            'specific_exercise' => 'For the next week: Every time you make a statement, immediately follow with "For example..." and provide a specific, concrete instance.',
        ]);
    }

    /**
     * Create avoidance report
     */
    private function createAvoidanceReport(UserProfile $profile, Collection $sessions): PatternReport
    {
        $avoidedTopics = $sessions
            ->pluck('topics_avoided')
            ->filter()
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        return PatternReport::create([
            'user_profile_id' => $profile->id,
            'pattern_type' => 'avoidance_pattern',
            'description' => 'Consistent avoidance of specific topics when conversation becomes uncomfortable.',
            'evidence' => $avoidedTopics,
            'correction_strategy' => 'Direct confrontation. Name what you are avoiding and why. Discomfort is data.',
            'specific_exercise' => 'Pick one avoided topic. Write three concrete paragraphs about it. No metaphors, no deflection.',
        ]);
    }

    /**
     * Create grandiosity report
     */
    private function createGrandiosityReport(UserProfile $profile, Collection $sessions): PatternReport
    {
        $evidence = $sessions
            ->where('grandiosity_detected', true)
            ->map(fn($s) => "Session " . $s->id . ": Grandiose claims unsupported by action")
            ->values()
            ->toArray();

        return PatternReport::create([
            'user_profile_id' => $profile->id,
            'pattern_type' => 'grandiosity',
            'description' => 'Repeated pattern of making grand claims without corresponding action or evidence.',
            'evidence' => $evidence,
            'correction_strategy' => 'Action precedes narrative. Show the work before making the claim.',
            'specific_exercise' => 'List three recent claims you made. For each, provide concrete evidence or admit absence of action.',
        ]);
    }

    /**
     * Create self-mythologizing report
     */
    private function createMythologizingReport(UserProfile $profile, Collection $sessions): PatternReport
    {
        $evidence = $sessions
            ->where('self_mythologizing_detected', true)
            ->map(fn($s) => "Session " . $s->id . ": Romanticization of struggle or identity")
            ->values()
            ->toArray();

        return PatternReport::create([
            'user_profile_id' => $profile->id,
            'pattern_type' => 'self_mythologizing',
            'description' => 'Tendency to romanticize struggle or construct narrative identity rather than confront reality.',
            'evidence' => $evidence,
            'correction_strategy' => 'Strip the story. What actually happened? Remove the drama and report facts.',
            'specific_exercise' => 'Rewrite one recent "struggle narrative" as a police report: Just facts, times, actions. No interpretation.',
        ]);
    }

    /**
     * Create repetitive themes report
     */
    private function createRepetitiveThemesReport(UserProfile $profile, Collection $poems, array $themes): PatternReport
    {
        return PatternReport::create([
            'user_profile_id' => $profile->id,
            'pattern_type' => 'repetitive_themes',
            'description' => 'You keep returning to the same thematic territory without deepening exploration.',
            'evidence' => $themes,
            'correction_strategy' => 'Force thematic diversity. Next three poems must explore entirely different subjects.',
            'specific_exercise' => 'Write a poem about something you have never written about before. No familiar emotional territory.',
        ]);
    }

    /**
     * Create rhythm instability report
     */
    private function createRhythmInstabilityReport(UserProfile $profile, Collection $poems): PatternReport
    {
        $evidence = $poems
            ->map(fn($p) => "Week {$p->week_number}: Rhythm breaks in lines 5-8")
            ->toArray();

        return PatternReport::create([
            'user_profile_id' => $profile->id,
            'pattern_type' => 'rhythm_instability',
            'description' => 'Consistent rhythm breaks mid-poem. You start strong then lose control.',
            'evidence' => $evidence,
            'correction_strategy' => 'Read aloud. Every line. Mark where breath breaks. Maintain cadence throughout.',
            'specific_exercise' => 'Next poem: Read every line aloud before writing the next. Record yourself.',
        ]);
    }

    /**
     * Create sentimental excess report
     */
    private function createSentimentalExcessReport(UserProfile $profile, Collection $poems): PatternReport
    {
        $evidence = $poems
            ->map(fn($p) => "Week {$p->week_number}: Emotional telling instead of showing")
            ->toArray();

        return PatternReport::create([
            'user_profile_id' => $profile->id,
            'pattern_type' => 'sentimental_excess',
            'description' => 'You tell the emotion instead of creating the conditions for it.',
            'evidence' => $evidence,
            'correction_strategy' => 'Remove all emotion words. Rebuild the poem with sensory detail only.',
            'specific_exercise' => 'Next poem: No words for emotions allowed. Only what can be seen, heard, touched.',
        ]);
    }

    /**
     * Extract themes from poems
     */
    private function extractThemes(Collection $poems): array
    {
        // Simplified theme extraction (in production, use smarter analysis)
        $commonWords = ['love', 'death', 'time', 'memory', 'loss', 'silence', 'dark', 'light'];
        $themes = [];

        foreach ($poems as $poem) {
            $content = strtolower($poem->content);
            foreach ($commonWords as $word) {
                if (str_contains($content, $word)) {
                    $themes[] = "Week {$poem->week_number}: '{$word}' theme";
                }
            }
        }

        return $themes;
    }

    /**
     * Check if themes are repetitive
     */
    private function hasRepetitiveThemes(array $themes): bool
    {
        $counts = array_count_values($themes);
        return max($counts) >= self::PATTERN_THRESHOLD;
    }

    /**
     * Check for rhythm instability
     */
    private function hasRhythmInstability(Collection $poems): bool
    {
        $instabilityCount = 0;
        
        foreach ($poems as $poem) {
            if ($poem->critique && isset($poem->critique['rhythm'])) {
                if (str_contains(strtolower($poem->critique['rhythm']), 'inconsistent')) {
                    $instabilityCount++;
                }
            }
        }

        return $instabilityCount >= self::PATTERN_THRESHOLD;
    }

    /**
     * Check for sentimental excess
     */
    private function hasSentimentalExcess(Collection $poems): bool
    {
        $excessCount = 0;
        
        foreach ($poems as $poem) {
            if ($poem->critique && isset($poem->critique['emotional_honesty'])) {
                if (str_contains(strtolower($poem->critique['emotional_honesty']), 'sentimental')) {
                    $excessCount++;
                }
            }
        }

        return $excessCount >= self::PATTERN_THRESHOLD;
    }

    /**
     * Get unacknowledged patterns for user
     */
    public function getUnacknowledgedPatterns(UserProfile $profile): Collection
    {
        return $profile->patternReports()
            ->where('acknowledged', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Generate pattern report summary
     */
    public function generatePatternReportSummary(UserProfile $profile): string
    {
        $patterns = $this->analyzePatterns($profile);

        if ($patterns->isEmpty()) {
            return "No significant patterns detected yet. Continue building data.";
        }

        $report = "PATTERN REPORT\n\n";
        
        foreach ($patterns as $pattern) {
            $report .= "Type: {$pattern->pattern_type}\n";
            $report .= "Description: {$pattern->description}\n\n";
            $report .= "Evidence:\n" . $pattern->getFormattedEvidence() . "\n\n";
            $report .= "Correction: {$pattern->correction_strategy}\n\n";
            $report .= "Exercise: {$pattern->specific_exercise}\n\n";
            $report .= "---\n\n";
        }

        return $report;
    }
}
