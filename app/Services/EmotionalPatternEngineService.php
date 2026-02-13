<?php

namespace App\Services;

use App\Models\Session;
use App\Models\UserProfile;
use App\Models\EmotionalSnapshot;

/**
 * EmotionalPatternEngineService
 * 
 * LAYER 2 — EMOTIONAL PATTERN ENGINE
 * 
 * Computes and updates emotional metrics:
 * - emotional_baseline
 * - volatility_score
 * - absolutist_language_frequency
 * - self_criticism_index
 * - recurring_topics[]
 * - time_of_day_emotional_drift
 * - session_depth_score
 * 
 * Uses lightweight NLP parsing to detect:
 * - Absolutes (always, never, nothing, everything)
 * - Negative self-references
 * - Emotional intensity words
 * - Repetition patterns
 */
class EmotionalPatternEngineService
{
    // Absolutist language patterns
    private const ABSOLUTIST_WORDS = [
        'always', 'never', 'nothing', 'everything', 'everyone', 'nobody',
        'none', 'all', 'every', 'completely', 'totally', 'absolutely',
        'entirely', 'constantly', 'forever', 'impossible', 'inevitable'
    ];

    // Self-criticism patterns
    private const SELF_CRITICISM_PATTERNS = [
        '/\bi\'m (so|too|really|very) (stupid|dumb|worthless|useless|pathetic|bad|terrible|awful)\b/i',
        '/\bi (hate|despise|loathe) myself\b/i',
        '/\bi can\'t (do|handle|manage|cope with) (anything|this|it)\b/i',
        '/\bi\'m (such a|a total|a complete) (failure|loser|mess|disaster)\b/i',
        '/\bwhat\'s wrong with me\b/i',
        '/\bi (always|never) (mess|screw|fuck) (up|things|everything|it) up\b/i',
        '/\bi\'m not (good|smart|capable|strong) enough\b/i',
    ];

    // Emotional intensity words
    private const INTENSITY_WORDS = [
        'devastated', 'crushed', 'destroyed', 'shattered', 'broken',
        'overwhelmed', 'drowning', 'suffocating', 'trapped', 'stuck',
        'hopeless', 'helpless', 'desperate', 'terrified', 'panicking',
        'collapsing', 'falling apart', 'can\'t breathe', 'losing it'
    ];

    /**
     * Analyze a message for emotional patterns
     */
    public function analyzeMessage(string $message): array
    {
        $message = strtolower($message);

        return [
            'absolutist_count' => $this->countAbsolutists($message),
            'self_criticism_count' => $this->countSelfCriticism($message),
            'intensity_score' => $this->calculateIntensity($message),
            'topics' => $this->extractTopics($message),
            'tone' => $this->calculateTone($message),
        ];
    }

    /**
     * Count absolutist language instances
     */
    private function countAbsolutists(string $message): int
    {
        $count = 0;
        foreach (self::ABSOLUTIST_WORDS as $word) {
            $count += substr_count($message, $word);
        }
        return $count;
    }

    /**
     * Count self-criticism instances
     */
    private function countSelfCriticism(string $message): int
    {
        $count = 0;
        foreach (self::SELF_CRITICISM_PATTERNS as $pattern) {
            $count += preg_match_all($pattern, $message);
        }
        return $count;
    }

    /**
     * Calculate emotional intensity (0-1 scale)
     */
    private function calculateIntensity(string $message): float
    {
        $intensityCount = 0;
        foreach (self::INTENSITY_WORDS as $word) {
            $intensityCount += substr_count($message, $word);
        }

        // Also check for repeated punctuation (!!! or ???)
        $exclamationCount = substr_count($message, '!!');
        $questionCount = substr_count($message, '??');
        $intensityCount += $exclamationCount + $questionCount;

        // Normalize to 0-1 scale
        return min(1.0, $intensityCount * 0.2);
    }

    /**
     * Extract emotional topics from message
     */
    private function extractTopics(string $message): array
    {
        $topics = [];

        // Simple keyword-based topic extraction
        $topicPatterns = [
            'work' => '/\b(work|job|career|boss|colleague|office|meeting)\b/i',
            'relationships' => '/\b(relationship|partner|spouse|girlfriend|boyfriend|family|parent|friend)\b/i',
            'anxiety' => '/\b(anxious|anxiety|worried|worry|nervous|stress|panic)\b/i',
            'depression' => '/\b(depressed|depression|sad|sadness|empty|numb|hopeless)\b/i',
            'self-worth' => '/\b(worthless|unworthy|inadequate|failure|not good enough|useless)\b/i',
            'loneliness' => '/\b(lonely|alone|isolated|disconnected|nobody)\b/i',
            'future' => '/\b(future|tomorrow|next|plan|uncertain|unclear)\b/i',
            'past' => '/\b(past|before|used to|regret|mistake|should have)\b/i',
            'identity' => '/\b(who am i|myself|identity|purpose|meaning|point)\b/i',
        ];

        foreach ($topicPatterns as $topic => $pattern) {
            if (preg_match($pattern, $message)) {
                $topics[] = $topic;
            }
        }

        return $topics;
    }

    /**
     * Calculate emotional tone (0-1 scale, where 0 = negative, 1 = positive)
     */
    private function calculateTone(string $message): float
    {
        $positiveWords = ['good', 'better', 'happy', 'hope', 'hopeful', 'grateful', 'thankful', 'ok', 'okay', 'fine', 'well'];
        $negativeWords = ['bad', 'worse', 'sad', 'terrible', 'awful', 'hate', 'angry', 'frustrated', 'upset', 'hurt'];

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            $positiveCount += substr_count($message, $word);
        }

        foreach ($negativeWords as $word) {
            $negativeCount += substr_count($message, $word);
        }

        // Calculate tone (0-1 scale)
        $total = $positiveCount + $negativeCount;
        if ($total === 0) {
            return 0.5; // Neutral
        }

        return $positiveCount / $total;
    }

    /**
     * Update session metrics based on message analysis
     */
    public function updateSessionMetrics(Session $session, array $analysis): void
    {
        $session->increment('message_count');
        $session->increment('absolutist_count', $analysis['absolutist_count']);
        $session->increment('self_criticism_count', $analysis['self_criticism_count']);

        // Update session intensity (weighted average)
        $currentIntensity = $session->session_intensity ?? 0.0;
        $messageCount = $session->message_count;
        $newIntensity = ($currentIntensity * ($messageCount - 1) + $analysis['intensity_score']) / $messageCount;
        $session->session_intensity = $newIntensity;

        // Update emotional tone (weighted average)
        $currentTone = $session->emotional_tone ?? 0.5;
        $newTone = ($currentTone * ($messageCount - 1) + $analysis['tone']) / $messageCount;
        $session->emotional_tone = $newTone;

        // Merge topics
        $existingTopics = $session->detected_topics ?? [];
        $newTopics = array_unique(array_merge($existingTopics, $analysis['topics']));
        $session->detected_topics = $newTopics;

        $session->last_message_at = now();
        $session->save();
    }

    /**
     * Update user profile metrics based on session
     */
    public function updateProfileMetrics(UserProfile $profile, Session $session): void
    {
        // Get recent emotional snapshots for trend analysis
        $recentSnapshots = $profile->emotionalSnapshots()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate emotional baseline (average tone across sessions)
        $totalTone = $session->emotional_tone ?? 0.5;
        $count = 1;
        foreach ($recentSnapshots as $snapshot) {
            $totalTone += $snapshot->tone;
            $count++;
        }
        $profile->emotional_baseline = $totalTone / $count;

        // Calculate volatility score (variance in intensity)
        $intensities = [$session->session_intensity ?? 0.0];
        foreach ($recentSnapshots as $snapshot) {
            $intensities[] = $snapshot->intensity;
        }
        $profile->volatility_score = $this->calculateVariance($intensities);

        // Update absolutist language frequency
        $profile->absolutist_language_frequency = $session->absolutist_count;

        // Update self-criticism index (normalized)
        $profile->self_criticism_index = min(1.0, $session->self_criticism_count / max(1, $session->message_count));

        // Merge recurring topics
        $existingTopics = $profile->recurring_topics ?? [];
        $sessionTopics = $session->detected_topics ?? [];
        $allTopics = array_merge($existingTopics, $sessionTopics);
        
        // Count topic frequency
        $topicFrequency = array_count_values($allTopics);
        arsort($topicFrequency);
        
        // Keep top 5 recurring topics
        $profile->recurring_topics = array_slice(array_keys($topicFrequency), 0, 5);

        // Update time of day emotional drift
        $hour = now()->hour;
        $timeOfDayDrift = $profile->time_of_day_emotional_drift ?? [];
        $timeOfDayDrift[$hour] = $session->emotional_tone ?? 0.5;
        $profile->time_of_day_emotional_drift = $timeOfDayDrift;

        // Calculate session depth score (based on message count and intensity)
        $profile->session_depth_score = min(1.0, ($session->message_count / 20) * ($session->session_intensity ?? 0.5));

        $profile->save();
    }

    /**
     * Create emotional snapshot at session end
     */
    public function createSnapshot(Session $session): EmotionalSnapshot
    {
        return EmotionalSnapshot::create([
            'user_profile_id' => $session->user_profile_id,
            'session_id' => $session->id,
            'intensity' => $session->session_intensity ?? 0.0,
            'tone' => $session->emotional_tone ?? 0.5,
            'absolutist_count' => $session->absolutist_count ?? 0,
            'self_criticism_count' => $session->self_criticism_count ?? 0,
            'topics' => $session->detected_topics ?? [],
            'hour_of_day' => now()->hour,
            'created_at' => now(),
        ]);
    }

    /**
     * Calculate variance for volatility score
     */
    private function calculateVariance(array $values): float
    {
        if (count($values) < 2) {
            return 0.0;
        }

        $mean = array_sum($values) / count($values);
        $variance = 0.0;

        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }

        return sqrt($variance / count($values)); // Standard deviation
    }

    /**
     * Generate observational insights based on aggregated metrics
     */
    public function generateInsights(UserProfile $profile): array
    {
        $insights = [];

        // Insight about emotional baseline
        if ($profile->emotional_baseline < 0.3) {
            $insights[] = "There's a persistent weight you carry.";
        } elseif ($profile->emotional_baseline > 0.7) {
            $insights[] = "Most days, you seem to find your footing.";
        }

        // Insight about volatility
        if ($profile->volatility_score > 0.5) {
            $insights[] = "Your emotional landscape shifts quickly.";
        }

        // Insight about absolutist thinking
        if ($profile->absolutist_language_frequency > 5) {
            $insights[] = "You speak in absolutes more than most.";
        }

        // Insight about self-criticism
        if ($profile->self_criticism_index > 0.3) {
            $insights[] = "You're harder on yourself than you need to be.";
        }

        // Insight about recurring topics
        if (!empty($profile->recurring_topics)) {
            $topTopic = $profile->recurring_topics[0];
            $insights[] = "You keep circling back to {$topTopic}.";
        }

        return $insights;
    }
}
