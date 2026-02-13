<?php

namespace App\Services;

use App\Models\UserProfile;
use App\Models\Session;

/**
 * FeatureButtonService - RANDOM / THOUGHTS / ADJACENT
 * 
 * Handles the three feature buttons:
 * - RANDOM: Constraint-based writing challenges
 * - THOUGHTS: Sharp philosophical prompts
 * - ADJACENT: Contrasting angles and deeper layers
 */
class FeatureButtonService
{
    /**
     * Generate constraint-based writing challenge (RANDOM)
     */
    public function generateRandomChallenge(): array
    {
        $challenges = [
            [
                'type' => 'constraint',
                'title' => 'Monosyllabic Constraint',
                'description' => 'Write 50 words using only single-syllable words. No exceptions.',
            ],
            [
                'type' => 'constraint',
                'title' => 'Reverse Chronology',
                'description' => 'Describe an event backwards. Start with the end, work toward the beginning. Maintain coherence.',
            ],
            [
                'type' => 'constraint',
                'title' => 'No Verbs Challenge',
                'description' => 'Write a paragraph without using any verbs. Force yourself into compressed noun phrases.',
            ],
            [
                'type' => 'constraint',
                'title' => 'Third-Person Objectivity',
                'description' => 'Describe your current emotional state in third person. Clinical detachment only.',
            ],
            [
                'type' => 'constraint',
                'title' => 'Sensory Isolation',
                'description' => 'Describe a memory using only one sense. No visual imagery allowed.',
            ],
            [
                'type' => 'constraint',
                'title' => 'Five-Word Sentences',
                'description' => 'Write 10 sentences. Each exactly five words. No variation.',
            ],
            [
                'type' => 'constraint',
                'title' => 'Opposite POV',
                'description' => 'Take your strongest opinion. Argue the opposite position with full conviction.',
            ],
            [
                'type' => 'constraint',
                'title' => 'Concrete Object Focus',
                'description' => 'Choose one physical object. Describe it for 200 words without metaphor or interpretation.',
            ],
            [
                'type' => 'constraint',
                'title' => 'Single Letter Removal',
                'description' => 'Write 100 words without using the letter "e". Full coherence required.',
            ],
            [
                'type' => 'constraint',
                'title' => 'Dialogue Only',
                'description' => 'Tell a complete story using only dialogue. No narration, no description.',
            ],
        ];

        return $challenges[array_rand($challenges)];
    }

    /**
     * Generate sharp philosophical prompt (THOUGHTS)
     */
    public function generateThoughtPrompt(): array
    {
        $prompts = [
            [
                'prompt' => 'You cannot optimize what you refuse to measure.',
                'challenge' => 'What are you avoiding quantifying?',
            ],
            [
                'prompt' => 'Comfort is data about what you already know.',
                'challenge' => 'What discomfort are you ignoring?',
            ],
            [
                'prompt' => 'The narrative you tell yourself is not evidence.',
                'challenge' => 'What story are you repeating without verification?',
            ],
            [
                'prompt' => 'Every excuse is a decision disguised as circumstance.',
                'challenge' => 'Name three excuses. Reframe them as choices.',
            ],
            [
                'prompt' => 'You are not your potential. You are your pattern.',
                'challenge' => 'What pattern are you actually executing?',
            ],
            [
                'prompt' => 'Intention without action is delusion.',
                'challenge' => 'List intentions from last month. Which have evidence?',
            ],
            [
                'prompt' => 'The question you avoid is the one that matters.',
                'challenge' => 'What question have you been circling without asking?',
            ],
            [
                'prompt' => 'Understanding is not the same as change.',
                'challenge' => 'What do you understand but refuse to alter?',
            ],
            [
                'prompt' => 'Your constraints reveal more than your capabilities.',
                'challenge' => 'What limits have you accepted without testing?',
            ],
            [
                'prompt' => 'The work you postpone owns you.',
                'challenge' => 'What task have you been deferring for over a month?',
            ],
        ];

        return $prompts[array_rand($prompts)];
    }

    /**
     * Generate adjacent theme or reframing (ADJACENT)
     */
    public function generateAdjacentTheme(Session $session): array
    {
        $detectedTopics = $session->detected_topics ?? [];
        
        if (empty($detectedTopics)) {
            return [
                'type' => 'reframe',
                'title' => 'General Reframe',
                'content' => 'What would change if you stopped treating this as a problem to solve and started treating it as data to observe?',
            ];
        }

        $primaryTopic = $detectedTopics[0];

        // Generate contrasting angles based on common topics
        $adjacentMappings = [
            'work' => [
                'type' => 'contrast',
                'title' => 'Work → Play Inversion',
                'content' => 'You framed this as work. What if it were play? How would your approach change?',
            ],
            'anxiety' => [
                'type' => 'deeper_layer',
                'title' => 'Anxiety → Control Analysis',
                'content' => 'Anxiety often masks desire for control. What specifically are you trying to control? Is it controllable?',
            ],
            'creativity' => [
                'type' => 'constraint',
                'title' => 'Creativity → Constraint Paradox',
                'content' => 'You seek creativity. Constraint often generates it better than freedom. What constraint could you add?',
            ],
            'identity' => [
                'type' => 'structural',
                'title' => 'Identity → Behavior Shift',
                'content' => 'Identity is fluid. Actions are concrete. What behavior would you change if identity were irrelevant?',
            ],
            'purpose' => [
                'type' => 'reframe',
                'title' => 'Purpose → Pattern Recognition',
                'content' => 'Purpose is often backward-looking pattern recognition. What patterns actually exist in your actions?',
            ],
        ];

        // Return adjacent theme or default reframe
        return $adjacentMappings[$primaryTopic] ?? [
            'type' => 'reframe',
            'title' => 'Perspective Shift',
            'content' => "You're approaching {$primaryTopic} from inside it. Step outside. What does it look like from distance?",
        ];
    }

    /**
     * Generate random prompt with context awareness
     */
    public function generateContextualRandom(UserProfile $profile): array
    {
        // Check if user has active discipline contract
        $contract = $profile->activeDisciplineContract();
        
        if ($contract) {
            // Include poetry-specific challenges
            $poetryChallenges = [
                [
                    'type' => 'poetry_constraint',
                    'title' => 'Enjambment Exercise',
                    'description' => 'Write 10 lines where every line breaks mid-phrase. No end-stopped lines.',
                ],
                [
                    'type' => 'poetry_constraint',
                    'title' => 'Image Chain',
                    'description' => 'Start with one concrete image. Each line must contain a new image that physically connects to the previous.',
                ],
                [
                    'type' => 'poetry_constraint',
                    'title' => 'Verb Density',
                    'description' => 'Write 8 lines where every line contains at least 3 active verbs. No passive voice.',
                ],
            ];

            // 50% chance of poetry challenge if contract active
            if (rand(0, 1)) {
                return $poetryChallenges[array_rand($poetryChallenges)];
            }
        }

        return $this->generateRandomChallenge();
    }
}
