<?php

namespace App\Services;

use App\Models\UserProfile;
use App\Models\NarrativeReflection;
use App\Models\EmotionalSnapshot;

/**
 * NarrativeContinuityEngineService
 * 
 * LAYER 3 — NARRATIVE CONTINUITY ENGINE
 * 
 * Every 5 sessions:
 * - Generate pattern reflection (3 observations)
 * - Highlight 1 contradiction
 * - Offer 1 long-term philosophical question
 * 
 * Constraints:
 * - Avoid therapy framing
 * - Avoid clinical labels
 * - Maintain philosophical tone
 */
class NarrativeContinuityEngineService
{
    /**
     * Check if user needs a reflection and generate it
     */
    public function generateReflectionIfNeeded(UserProfile $profile): ?NarrativeReflection
    {
        if (!$profile->needsReflection()) {
            return null;
        }

        // Get recent snapshots (last 5 sessions)
        $recentSnapshots = $profile->emotionalSnapshots()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($recentSnapshots->count() < 5) {
            return null;
        }

        // Generate reflection
        $observations = $this->generateObservations($profile, $recentSnapshots);
        $contradiction = $this->identifyContradiction($profile, $recentSnapshots);
        $question = $this->generatePhilosophicalQuestion($profile, $recentSnapshots);

        $reflection = NarrativeReflection::create([
            'user_profile_id' => $profile->id,
            'pattern_observations' => $observations,
            'identified_contradiction' => $contradiction,
            'philosophical_question' => $question,
            'shown_to_user' => false,
            'created_at' => now(),
        ]);

        // Reset counter
        $profile->resetReflectionCounter();

        return $reflection;
    }

    /**
     * Generate 3 pattern observations based on metrics
     */
    private function generateObservations(UserProfile $profile, $snapshots): array
    {
        $observations = [];

        // Observation 1: Emotional trajectory
        $tones = $snapshots->pluck('tone')->toArray();
        $toneTrend = $this->calculateTrend($tones);
        
        if ($toneTrend > 0.1) {
            $observations[] = "Over these last conversations, something has been lifting. The edge is softer.";
        } elseif ($toneTrend < -0.1) {
            $observations[] = "These past sessions have carried more weight. The darkness has been closer.";
        } else {
            $observations[] = "You've been holding steady, but there's a tension in the balance.";
        }

        // Observation 2: Pattern of intensity
        $intensities = $snapshots->pluck('intensity')->toArray();
        $avgIntensity = array_sum($intensities) / count($intensities);
        
        if ($avgIntensity > 0.7) {
            $observations[] = "You come here when things are overwhelming. This has become a pressure valve.";
        } elseif ($avgIntensity < 0.3) {
            $observations[] = "Our conversations have been quiet — almost exploratory. Less crisis, more wondering.";
        } else {
            $observations[] = "You arrive somewhere between turbulence and calm, seeking equilibrium.";
        }

        // Observation 3: Recurring theme
        $topicCounts = [];
        foreach ($snapshots as $snapshot) {
            foreach ($snapshot->topics as $topic) {
                $topicCounts[$topic] = ($topicCounts[$topic] ?? 0) + 1;
            }
        }
        
        if (!empty($topicCounts)) {
            arsort($topicCounts);
            $topTopic = array_key_first($topicCounts);
            $observations[] = $this->generateTopicObservation($topTopic, $topicCounts[$topTopic]);
        } else {
            $observations[] = "You speak around the edges of things, never quite naming the center.";
        }

        return $observations;
    }

    /**
     * Generate topic-specific observation
     */
    private function generateTopicObservation(string $topic, int $frequency): string
    {
        $observationTemplates = [
            'work' => [
                "Work keeps coming up — not as complaint, but as existential question.",
                "You return to work like testing a bruise. Is it still tender? Still there?",
            ],
            'relationships' => [
                "Relationships are where you lose and find yourself in equal measure.",
                "You speak of connection like someone studying a language they half-remember.",
            ],
            'anxiety' => [
                "Anxiety is your constant companion. Sometimes loud, sometimes just breathing nearby.",
                "You describe anxiety like weather — inevitable, unmappable, arriving without warning.",
            ],
            'depression' => [
                "Depression sits in these conversations like furniture. Familiar. Heavy. Assumed.",
                "You mention darkness the way some people mention the news. Factual. Ever-present.",
            ],
            'self-worth' => [
                "Self-worth is the axis you keep rotating around. Who am I? Am I enough?",
                "You question your own value like it's a philosophical problem, not a given.",
            ],
            'loneliness' => [
                "Loneliness is the thread running through these sessions. Not dramatic. Just persistent.",
                "You describe being alone like being underwater — surrounded, but separate.",
            ],
            'future' => [
                "The future feels like a weight rather than a promise. Uncertainty as burden.",
                "You think about tomorrow with the dread of someone expecting inevitable disappointment.",
            ],
            'past' => [
                "The past keeps its hooks in you. You revisit it like evidence you can't quite interpret.",
                "Memory is where you go to understand now. Or maybe to hide from it.",
            ],
            'identity' => [
                "Who you are feels like a question you're tired of asking but can't stop asking.",
                "Identity is slippery for you. You keep trying to hold yourself still long enough to see.",
            ],
        ];

        $templates = $observationTemplates[$topic] ?? ["You keep returning to {$topic}, circling it like something you can't quite land on."];
        return $templates[array_rand($templates)];
    }

    /**
     * Identify a contradiction in patterns
     */
    private function identifyContradiction(UserProfile $profile, $snapshots): string
    {
        $contradictions = [];

        // Check for tone vs intensity contradiction
        $tones = $snapshots->pluck('tone')->toArray();
        $intensities = $snapshots->pluck('intensity')->toArray();
        
        $avgTone = array_sum($tones) / count($tones);
        $avgIntensity = array_sum($intensities) / count($intensities);
        
        if ($avgTone > 0.6 && $avgIntensity > 0.7) {
            $contradictions[] = "You speak hopefully while drowning in overwhelm. Which one is the lie?";
        } elseif ($avgTone < 0.4 && $avgIntensity < 0.3) {
            $contradictions[] = "You report darkness but arrive calm. Is the numbness protection or symptom?";
        }

        // Check for volatility vs baseline contradiction
        if ($profile->volatility_score > 0.6 && $profile->emotional_baseline > 0.6) {
            $contradictions[] = "You swing wildly between states but insist you're fine. Stability might be the mask.";
        }

        // Check for self-criticism vs depth contradiction
        if ($profile->self_criticism_index > 0.4 && $profile->session_depth_score > 0.7) {
            $contradictions[] = "You tear yourself apart while doing profound inner work. Self-attack as familiar ritual?";
        }

        if (empty($contradictions)) {
            return "You seek coherence but resist commitment to any single narrative about yourself.";
        }

        return $contradictions[array_rand($contradictions)];
    }

    /**
     * Generate a long-term philosophical question
     */
    private function generatePhilosophicalQuestion(UserProfile $profile, $snapshots): string
    {
        $questions = [];

        // Questions based on volatility
        if ($profile->volatility_score > 0.6) {
            $questions[] = "What if the volatility isn't a problem to fix, but information to decode?";
            $questions[] = "Is stability worth pursuing, or is flux your natural state?";
        }

        // Questions based on recurring topics
        if (!empty($profile->recurring_topics)) {
            $topTopic = $profile->recurring_topics[0];
            $questions = array_merge($questions, $this->generateTopicQuestions($topTopic));
        }

        // Questions based on self-criticism
        if ($profile->self_criticism_index > 0.4) {
            $questions[] = "What would happen if you treated yourself like someone you were trying to understand?";
            $questions[] = "Who taught you that you needed to earn existence?";
        }

        // Questions based on emotional baseline
        if ($profile->emotional_baseline < 0.3) {
            $questions[] = "What does 'okay' even look like for you? Have you forgotten, or never known?";
            $questions[] = "If this heaviness lifted, would you recognize yourself?";
        }

        // Default questions
        if (empty($questions)) {
            $questions[] = "What are you avoiding by staying in motion?";
            $questions[] = "What truth are you protecting by keeping this story about yourself?";
        }

        return $questions[array_rand($questions)];
    }

    /**
     * Generate topic-specific philosophical questions
     */
    private function generateTopicQuestions(string $topic): array
    {
        $questionTemplates = [
            'work' => [
                "Is work meaning, or escape from meaning?",
                "What are you proving by working? And to whom?",
            ],
            'relationships' => [
                "Do you choose isolation, or does it choose you?",
                "What would intimacy require you to risk?",
            ],
            'anxiety' => [
                "What if anxiety is telling the truth, just poorly?",
                "Is the fear protecting something, or destroying it?",
            ],
            'depression' => [
                "What is depression preserving by keeping you still?",
                "If you stopped fighting it, what would it tell you?",
            ],
            'self-worth' => [
                "Whose voice is it that tells you you're not enough?",
                "What would it cost to believe you matter without proof?",
            ],
            'loneliness' => [
                "Are you alone because you must be, or because it's familiar?",
                "What does connection mean to you — really?",
            ],
            'future' => [
                "Is the future frightening, or is it planning for the future?",
                "What if you stopped trying to control what comes next?",
            ],
            'past' => [
                "What are you trying to retrieve from memory?",
                "Is the past evidence, or is it mythology?",
            ],
            'identity' => [
                "What if you're not lost, just becoming?",
                "Who would you be without this story you keep telling?",
            ],
        ];

        return $questionTemplates[$topic] ?? [
            "What are you not saying, even to yourself?",
            "What changes when you name this out loud?",
        ];
    }

    /**
     * Calculate trend (positive = improving, negative = declining)
     */
    private function calculateTrend(array $values): float
    {
        if (count($values) < 2) {
            return 0.0;
        }

        // Simple linear regression slope
        $n = count($values);
        $sumX = array_sum(range(0, $n - 1));
        $sumY = array_sum($values);
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($values as $i => $y) {
            $sumXY += $i * $y;
            $sumX2 += $i * $i;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        return $slope;
    }

    /**
     * Get latest unshown reflection for user
     */
    public function getLatestReflection(UserProfile $profile): ?NarrativeReflection
    {
        return $profile->narrativeReflections()
            ->where('shown_to_user', false)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Generate philosophical prompt based on recurring themes (for Random button)
     */
    public function generateRandomPrompt(UserProfile $profile): string
    {
        $prompts = [
            "What are you carrying that doesn't belong to you?",
            "Where does thinking stop and rumination begin?",
            "What would it mean to forgive yourself?",
            "Is it safety you want, or the illusion of control?",
            "What do you know that you're pretending not to know?",
            "What breaks when you name it out loud?",
            "Where does fear serve you? Where does it lie?",
        ];

        // Add topic-specific prompts
        if (!empty($profile->recurring_topics)) {
            $topTopic = $profile->recurring_topics[0];
            $topicPrompts = $this->getTopicPrompts($topTopic);
            $prompts = array_merge($prompts, $topicPrompts);
        }

        return $prompts[array_rand($prompts)];
    }

    /**
     * Get topic-specific prompts
     */
    private function getTopicPrompts(string $topic): array
    {
        $topicPrompts = [
            'work' => [
                "What would you do if work didn't define you?",
                "Is ambition desire, or internalized expectation?",
            ],
            'relationships' => [
                "What does love ask of you that you're unwilling to give?",
                "Are you waiting to be chosen, or choosing to wait?",
            ],
            'anxiety' => [
                "What if the fear is justified, just disproportionate?",
                "What happens in the space between alarm and action?",
            ],
            'depression' => [
                "What is exhaustion protecting you from?",
                "If you weren't fighting it, what would you be doing?",
            ],
            'self-worth' => [
                "Whose approval are you still seeking?",
                "What if mattering isn't something you earn?",
            ],
            'loneliness' => [
                "Is solitude chosen or enforced?",
                "What kind of presence could you actually tolerate?",
            ],
        ];

        return $topicPrompts[$topic] ?? [];
    }

    /**
     * Generate reflection summary from current session (for Thoughts button)
     */
    public function generateSessionReflection(array $sessionTopics, float $intensity, float $tone): string
    {
        $reflection = "";

        // Comment on intensity
        if ($intensity > 0.7) {
            $reflection .= "This session had weight. You were working through something heavy. ";
        } elseif ($intensity > 0.4) {
            $reflection .= "You were present but measured today. ";
        } else {
            $reflection .= "This was a quieter exploration. ";
        }

        // Comment on tone
        if ($tone < 0.3) {
            $reflection .= "The emotional tone leaned dark. ";
        } elseif ($tone > 0.7) {
            $reflection .= "There was lightness in your words. ";
        }

        // Comment on topics
        if (!empty($sessionTopics)) {
            $reflection .= "You circled around " . implode(', ', array_slice($sessionTopics, 0, 2)) . ". ";
        }

        // Add philosophical closing
        $closings = [
            "Notice what you came with, and what you're leaving with.",
            "The patterns show themselves only when you're ready to see them.",
            "Sometimes awareness is enough. Action can wait.",
            "You don't have to solve everything tonight.",
        ];

        $reflection .= $closings[array_rand($closings)];

        return $reflection;
    }

    /**
     * Suggest adjacent theme based on recurring topics (for Adjacent button)
     */
    public function suggestAdjacentTheme(UserProfile $profile): string
    {
        if (empty($profile->recurring_topics)) {
            return "What's beneath the surface of what you're saying?";
        }

        $topTopic = $profile->recurring_topics[0];

        $adjacentMap = [
            'work' => "When work becomes identity, what happens to rest?",
            'relationships' => "Connection and autonomy — can you hold both?",
            'anxiety' => "Beneath the anxiety: what belief is being challenged?",
            'depression' => "Depression as signal — what is it signaling?",
            'self-worth' => "If you matter inherently, what does that change?",
            'loneliness' => "Loneliness vs. solitude: what's the difference for you?",
            'future' => "The future pulls you forward, but toward what?",
            'past' => "What is the past trying to teach you?",
            'identity' => "Who do you become when no one's watching?",
        ];

        return $adjacentMap[$topTopic] ?? "What are you circling without landing on?";
    }
}
