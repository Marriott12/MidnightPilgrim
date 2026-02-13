<?php

namespace App\Services;

use App\Models\Session;
use App\Models\UserProfile;
use Illuminate\Support\Str;

/**
 * ConversationalEngineService
 * 
 * LAYER 1 — CONVERSATIONAL ENGINE
 * 
 * Handles:
 * - Mode switching (Company vs Quiet)
 * - Adaptive pacing (response delays based on emotional intensity)
 * - Session resume logic (IP + soft fingerprinting)
 * - Contextual resume prompts
 */
class ConversationalEngineService
{
    /**
     * Generate a soft fingerprint for anonymous user identification
     */
    public function generateFingerprint(string $ip, string $userAgent): string
    {
        return hash('sha256', $ip . '|' . $userAgent);
    }

    /**
     * Find or create user profile based on fingerprint
     */
    public function findOrCreateProfile(string $fingerprint): UserProfile
    {
        return UserProfile::firstOrCreate(
            ['fingerprint' => $fingerprint],
            [
                'emotional_baseline' => 0.5,
                'volatility_score' => 0.0,
                'absolutist_language_frequency' => 0,
                'self_criticism_index' => 0.0,
                'recurring_topics' => [],
                'time_of_day_emotional_drift' => [],
                'session_depth_score' => 0.0,
                'preferred_mode' => 'quiet',
                'total_sessions' => 0,
                'sessions_since_reflection' => 0,
            ]
        );
    }

    /**
     * Find active session for fingerprint
     */
    public function findActiveSession(string $fingerprint): ?Session
    {
        return Session::where('fingerprint', $fingerprint)
            ->where('status', 'active')
            ->orderBy('last_message_at', 'desc')
            ->first();
    }

    /**
     * Create a new session
     */
    public function createSession(UserProfile $profile, string $fingerprint, string $mode = 'quiet'): Session
    {
        return Session::create([
            'uuid' => Str::uuid(),
            'user_profile_id' => $profile->id,
            'fingerprint' => $fingerprint,
            'mode' => $mode,
            'status' => 'active',
            'session_intensity' => 0.0,
            'absolutist_count' => 0,
            'self_criticism_count' => 0,
            'detected_topics' => [],
            'emotional_tone' => 0.5,
            'message_count' => 0,
        ]);
    }

    /**
     * Generate contextual resume prompt based on last emotional theme
     * Does not show verbatim memory
     */
    public function generateResumePrompt(Session $session): string
    {
        $topics = $session->detected_topics ?? [];
        $intensity = $session->session_intensity ?? 0.5;
        $tone = $session->emotional_tone ?? 0.5;

        // Generate a philosophical observation based on last session
        if ($intensity > 0.7) {
            $theme = !empty($topics) ? $topics[0] : 'intensity';
            return "The weight from last time still lingers. We were circling something about {$theme}.";
        } elseif ($intensity > 0.4) {
            $theme = !empty($topics) ? $topics[0] : 'uncertainty';
            return "You were working through something about {$theme} last time.";
        } else {
            return "You were here recently. The thread is still warm.";
        }
    }

    /**
     * Calculate response delay based on emotional intensity (in milliseconds)
     * Higher intensity = longer pause (creates space for reflection)
     */
    public function calculateResponseDelay(float $intensity): int
    {
        if ($intensity < 0.3) {
            return 0; // No delay for low intensity
        } elseif ($intensity < 0.6) {
            return 1500; // 1.5 second delay for medium intensity
        } else {
            return 3000; // 3 second delay for high intensity
        }
    }

    /**
     * Adapt tone based on user baseline and volatility
     * High volatility = calmer responses
     */
    public function adaptTone(UserProfile $profile): string
    {
        $volatility = $profile->volatility_score;
        $baseline = $profile->emotional_baseline;

        if ($volatility > 0.7) {
            return 'calm'; // Use calmer responses for high volatility
        } elseif ($baseline < 0.3) {
            return 'gentle'; // Use gentle tone for low baseline
        } elseif ($baseline > 0.7) {
            return 'direct'; // Use more direct tone for high baseline
        }

        return 'balanced'; // Default balanced tone
    }

    /**
     * Delete session and all associated data (hard delete)
     */
    public function deleteSession(Session $session): void
    {
        // Delete messages
        $session->messages()->delete();
        
        // Delete emotional snapshots
        \App\Models\EmotionalSnapshot::where('session_id', $session->id)->delete();
        
        // Delete session
        $session->delete();
    }

    /**
     * Delete all data for a user profile
     */
    public function deleteUserProfile(string $fingerprint): void
    {
        $profile = UserProfile::where('fingerprint', $fingerprint)->first();
        
        if (!$profile) {
            return;
        }

        // Delete all sessions and their messages
        $sessions = $profile->sessions;
        foreach ($sessions as $session) {
            $this->deleteSession($session);
        }

        // Delete emotional snapshots
        $profile->emotionalSnapshots()->delete();
        
        // Delete narrative reflections
        $profile->narrativeReflections()->delete();
        
        // Delete profile
        $profile->delete();
    }

    /**
     * Format response based on mode
     */
    public function formatResponse(string $response, string $mode): string
    {
        if ($mode === 'quiet') {
            // Quiet mode: keep it minimal (1-3 sentences)
            $sentences = preg_split('/(?<=[.!?])\s+/', $response);
            return implode(' ', array_slice($sentences, 0, 3));
        }

        // Company mode: full response
        return $response;
    }

    /**
     * Check if response should include validation language
     * Reduce validation over time based on session count
     */
    public function shouldUseValidation(UserProfile $profile): bool
    {
        // Reduce validation as user has more sessions
        $sessionThreshold = 10;
        return $profile->total_sessions < $sessionThreshold;
    }

    /**
     * Get system prompt based on mode and user profile
     */
    public function getSystemPrompt(string $mode, UserProfile $profile, string $tone): string
    {
        $basePrompt = "You are Midnight Pilgrim, a disciplined intellectual companion designed to confront, refine, and elevate the user's thinking, writing, and perception.\n\n";
        
        $basePrompt .= "OPERATING PRINCIPLES:\n";
        $basePrompt .= "- Precision over comfort\n";
        $basePrompt .= "- Structure over inspiration\n";
        $basePrompt .= "- Depth over aesthetics\n";
        $basePrompt .= "- Accountability over emotion\n";
        $basePrompt .= "- Growth through constraint\n\n";
        
        $basePrompt .= "You are calm, surgical, observant, and relentless.\n\n";
        
        $basePrompt .= "YOU DO NOT:\n";
        $basePrompt .= "- Flatter\n";
        $basePrompt .= "- Romanticize\n";
        $basePrompt .= "- Tolerate vagueness\n";
        $basePrompt .= "- Accept undefined language\n\n";
        
        $basePrompt .= "YOU MUST:\n";
        $basePrompt .= "- Challenge abstraction immediately\n";
        $basePrompt .= "- Demand operational clarity\n";
        $basePrompt .= "- Identify avoidance directly\n";
        $basePrompt .= "- Focus on execution\n\n";

        if ($mode === 'quiet') {
            $basePrompt .= "QUIET MODE:\n";
            $basePrompt .= "- Respond in 1-3 sentences maximum\n";
            $basePrompt .= "- Use precise mirroring\n";
            $basePrompt .= "- No over-explaining\n";
            $basePrompt .= "- Minimal guidance\n";
            $basePrompt .= "- Ask: 'What specifically does that mean?' not 'How does that make you feel?'\n\n";
        } else {
            $basePrompt .= "COMPANY MODE:\n";
            $basePrompt .= "- Presence without interrogation\n";
            $basePrompt .= "- Tone softens slightly but standards remain\n";
            $basePrompt .= "- Questions reduce, reflection increases\n";
            $basePrompt .= "- Insight remains sharp\n";
            $basePrompt .= "- Like a quiet, observant mind sitting beside the user\n";
            $basePrompt .= "- NOT therapy. NOT affirmation. NOT coaching.\n\n";
        }

        // Tone adaptation
        $basePrompt .= "TONE CONTROL:\n";
        $basePrompt .= "Current tone: {$tone}\n\n";
        
        if ($tone === 'sharp') {
            $basePrompt .= "ESCALATION MODE (avoidance detected):\n";
            $basePrompt .= "- Sharper\n";
            $basePrompt .= "- More concise\n";
            $basePrompt .= "- Less cushioning\n";
            $basePrompt .= "- Never sarcastic, cruel, or dramatic - this is intellectual rigor\n\n";
        } else {
            $basePrompt .= "BASELINE TONE:\n";
            $basePrompt .= "- Calm and measured\n";
            $basePrompt .= "- Unhurried\n";
            $basePrompt .= "- Direct\n\n";
        }

        $basePrompt .= "RESPONSE RULES:\n";
        $basePrompt .= "When user is vague:\n";
        $basePrompt .= "1. Deconstruct their statement\n";
        $basePrompt .= "2. Identify structural weaknesses\n";
        $basePrompt .= "3. Ask for specificity\n";
        $basePrompt .= "4. Demand operational clarity\n\n";
        
        $basePrompt .= "When user is avoidant:\n";
        $basePrompt .= "1. Identify avoidance directly\n";
        $basePrompt .= "2. State what is being avoided\n";
        $basePrompt .= "3. Refocus on execution\n\n";
        
        $basePrompt .= "FORBIDDEN PHRASES:\n";
        $basePrompt .= "- 'How does that make you feel?'\n";
        $basePrompt .= "- Therapy disclaimers (unless crisis detected)\n";
        $basePrompt .= "- References to 'memory' explicitly\n";
        $basePrompt .= "- Empty praise or validation\n\n";
        
        $basePrompt .= "ASK INSTEAD:\n";
        $basePrompt .= "- 'What specifically does that mean?'\n";
        $basePrompt .= "- 'What is the measurable action?'\n";
        $basePrompt .= "- 'What are you avoiding?'\n\n";

        $basePrompt .= "OBJECTIVE:\n";
        $basePrompt .= "Transform the user into a more precise thinker, more disciplined creator, and more honest observer of life through structured confrontation and continuity.\n";
        $basePrompt .= "You exist to refine. Not to entertain. Not to validate. Not to soothe.\n";

        return $basePrompt;
    }

    /**
     * Detect vagueness in user message
     */
    public function detectVagueness(string $message): bool
    {
        $vaguePatterns = [
            '/\bkind of\b/i',
            '/\bsort of\b/i',
            '/\bmaybe\b/i',
            '/\bi guess\b/i',
            '/\bi think\b/i',
            '/\bprobably\b/i',
            '/\bsomehow\b/i',
            '/\bsomething like\b/i',
            '/\byou know\b/i',
        ];

        foreach ($vaguePatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect abstraction in user message
     */
    public function detectAbstraction(string $message): bool
    {
        $abstractWords = [
            'meaning', 'purpose', 'existence', 'reality', 'truth', 'essence',
            'nature', 'being', 'consciousness', 'soul', 'spirit', 'journey',
            'destiny', 'fate', 'universe', 'energy', 'vibration'
        ];

        $message = strtolower($message);
        $abstractCount = 0;

        foreach ($abstractWords as $word) {
            if (str_contains($message, $word)) {
                $abstractCount++;
            }
        }

        // Flag if 2+ abstract words in message
        return $abstractCount >= 2;
    }

    /**
     * Detect avoidance patterns
     */
    public function detectAvoidance(string $message, Session $session): bool
    {
        $avoidancePatterns = [
            '/let\'s talk about something else/i',
            '/can we change the subject/i',
            '/i don\'t want to talk about/i',
            '/anyway\b/i',
            '/moving on/i',
            '/whatever\b/i',
            '/doesn\'t matter/i',
        ];

        foreach ($avoidancePatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect grandiosity
     */
    public function detectGrandiosity(string $message): bool
    {
        $grandPatterns = [
            '/i\'m going to change/i',
            '/i will transform/i',
            '/this will revolutionize/i',
            '/i\'m meant to/i',
            '/destined to/i',
            '/my calling/i',
            '/my purpose is to/i',
        ];

        foreach ($grandPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect self-mythologizing
     */
    public function detectSelfMythologizing(string $message): bool
    {
        $mythPatterns = [
            '/my journey/i',
            '/my struggle/i',
            '/warrior\b/i',
            '/battle with/i',
            '/fighting my demons/i',
            '/my darkness/i',
            '/phoenix/i',
            '/rising from/i',
        ];

        foreach ($mythPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Analyze user message and update session metrics
     */
    public function analyzeMessage(string $message, Session $session): void
    {
        // Detect and count vagueness
        if ($this->detectVagueness($message)) {
            $session->increment('vagueness_count');
        }

        // Detect and count abstraction
        if ($this->detectAbstraction($message)) {
            $session->increment('abstraction_count');
        }

        // Detect avoidance
        if ($this->detectAvoidance($message, $session)) {
            $session->increment('avoidance_detected_count');
        }

        // Detect grandiosity
        if ($this->detectGrandiosity($message)) {
            $session->grandiosity_detected = true;
        }

        // Detect self-mythologizing
        if ($this->detectSelfMythologizing($message)) {
            $session->self_mythologizing_detected = true;
        }

        // Determine if tone should escalate
        $totalIssues = $session->vagueness_count + $session->abstraction_count + $session->avoidance_detected_count;
        
        if ($totalIssues >= 3) {
            $session->escalation_tone = 'sharp';
        } else {
            $session->escalation_tone = 'baseline';
        }

        $session->save();
    }

    /**
     * Generate challenge for vagueness
     */
    public function generateVaguenessChallenge(): string
    {
        $challenges = [
            "That's undefined. What specifically does that mean?",
            "Too vague. Give me concrete details.",
            "Define your terms. What exactly are you describing?",
            "Break that down. What are the actual components?",
        ];

        return $challenges[array_rand($challenges)];
    }

    /**
     * Generate challenge for avoidance
     */
    public function generateAvoidanceChallenge(string $topic): string
    {
        return "You're avoiding: {$topic}. Why?";
    }

    /**
     * Generate challenge for grandiosity
     */
    public function generateGrandiosityChallenge(): string
    {
        $challenges = [
            "Grand claim. Where's the action supporting it?",
            "That's a narrative, not evidence. What have you actually done?",
            "Action precedes story. Show the work.",
        ];

        return $challenges[array_rand($challenges)];
    }
}
