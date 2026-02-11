<?php

namespace App\Services;

use App\Models\Session;
use App\Models\Message;
use App\Models\Note;
use App\Models\ShortLine;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

/**
 * ConversationService - SILENCE-FIRST CONVERSATION ENGINE
 * 
 * CORE PRINCIPLES:
 * - Anonymous identity (UUID in httpOnly cookie)
 * - NO IP tracking, NO analytics, NO engagement metrics
 * - Silence is valid and encouraged
 * - Two modes: quiet (minimal) and company (gentle)
 * - Contextual anchoring from notes (unlabeled)
 * - NO memory references, NO psychological interpretation
 * 
 * MODES:
 * - Quiet: Short responses, rare questions, reflective silence
 * - Company: Medium responses, gentle questions (max 1), tone mirroring
 */
class ConversationService
{
    /**
     * Get or create session for UUID
     */
    public function getOrCreateSession(string $uuid, string $mode = 'quiet'): Session
    {
        $session = Session::where('uuid', $uuid)
            ->where('status', 'active')
            ->first();

        if (!$session) {
            $session = Session::create([
                'uuid' => $uuid,
                'mode' => $mode,
                'status' => 'active',
            ]);
        }

        return $session;
    }

    /**
     * Check if UUID has an active session
     */
    public function hasActiveSession(string $uuid): bool
    {
        return Session::where('uuid', $uuid)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Get active session for UUID
     */
    public function getActiveSession(string $uuid): ?Session
    {
        return Session::where('uuid', $uuid)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Begin again - hard delete previous session
     */
    public function beginAgain(string $uuid, string $mode = 'quiet'): Session
    {
        $oldSession = $this->getActiveSession($uuid);
        
        if ($oldSession) {
            $oldSession->obliterate();
        }

        return $this->getOrCreateSession($uuid, $mode);
    }

    /**
     * Send message and get response with smart silence detection
     */
    public function sendMessage(Session $session, string $content): ?string
    {
        // Save user message
        $userMessage = Message::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => trim($content),
            'created_at' => now(),
        ]);

        // Smart silence detection
        if ($this->shouldRespondWithSilence($session, $content)) {
            // Save explicit silence marker
            Message::create([
                'session_id' => $session->id,
                'role' => 'assistant',
                'content' => '[silence:acknowledge]',
                'created_at' => now(),
            ]);
            return null;
        }

        // Build context
        $context = $this->buildContext($session, $content);

        // Generate response based on mode
        $response = $this->generateResponse($session->mode, $content, $context);

        // Save assistant message
        if ($response !== null) {
            Message::create([
                'session_id' => $session->id,
                'role' => 'assistant',
                'content' => $response,
                'created_at' => now(),
            ]);
        } else {
            // Explicit silence
            Message::create([
                'session_id' => $session->id,
                'role' => 'assistant',
                'content' => '[silence:reflect]',
                'created_at' => now(),
            ]);
        }

        return $response;
    }

    /**
     * Determine if silence is more appropriate than words
     */
    private function shouldRespondWithSilence(Session $session, string $content): bool
    {
        $trimmed = strtolower(trim($content));
        $wordCount = str_word_count($content);
        
        // Explicit silence requests
        if (in_array($trimmed, ['pause', 'enough', 'silence', 'quiet', ''])) {
            return true;
        }
        
        // Very short acknowledgments
        if (in_array($trimmed, ['yeah', 'ok', 'mhm', 'mm', 'yes', 'no', 'sure', 'k'])) {
            return true;
        }
        
        // Temporal awareness - late night prefers silence
        $hour = now()->hour;
        $isLateNight = $hour >= 23 || $hour <= 3;
        $isEarlyMorning = $hour >= 4 && $hour <= 6;
        
        // Check for repetitive patterns (user circling same topic)
        $recentMessages = $session->getRecentMessages(5);
        if ($this->detectRepetitivePattern($content, $recentMessages)) {
            return $session->mode === 'quiet' ? (rand(1, 2) === 1) : (rand(1, 4) === 1);
        }
        
        // Emotional saturation detection (excessive emotion words)
        if ($this->detectEmotionalSaturation($content)) {
            return $session->mode === 'quiet' ? (rand(1, 3) === 1) : false;
        }
        
        // Mode-based silence probability
        if ($session->mode === 'quiet') {
            // Higher silence chance late at night
            if ($isLateNight || $isEarlyMorning) {
                return rand(1, 3) === 1; // 33% silence
            }
            // Short messages in quiet mode often warrant silence
            if ($wordCount <= 3) {
                return rand(1, 2) === 1; // 50% silence
            }
        }
        
        return false;
    }

    /**
     * Detect if user is circling the same topic repeatedly
     */
    private function detectRepetitivePattern(string $currentInput, $recentMessages): bool
    {
        if (!$recentMessages || $recentMessages->count() < 3) {
            return false;
        }
        
        $currentKeywords = $this->extractKeywords($currentInput);
        $previousKeywords = [];
        
        foreach ($recentMessages->where('role', 'user') as $msg) {
            $previousKeywords = array_merge($previousKeywords, $this->extractKeywords($msg->content));
        }
        
        // Check overlap
        $overlap = array_intersect($currentKeywords, $previousKeywords);
        return count($overlap) >= min(2, count($currentKeywords));
    }

    /**
     * Detect emotional saturation (overwhelm)
     */
    private function detectEmotionalSaturation(string $content): bool
    {
        $emotionWords = ['feel', 'feeling', 'feelings', 'overwhelming', 'overwhelmed', 'too much', 
                         'cant', "can't", 'unable', 'difficult', 'hard', 'heavy', 'tired', 'exhausted'];
        
        $content = strtolower($content);
        $count = 0;
        
        foreach ($emotionWords as $word) {
            if (str_contains($content, $word)) {
                $count++;
            }
        }
        
        return $count >= 3; // 3+ emotion indicators
    }

    /**
     * Build conversation context with mode-aware intelligence
     */
    private function buildContext(Session $session, string $userInput): array
    {
        // Context window varies by mode
        $messageLimit = $session->mode === 'quiet' ? 5 : 15;
        $noteLimit = $session->mode === 'quiet' ? 2 : 5;
        
        // Get recent messages
        $recentMessages = $session->getRecentMessages($messageLimit);

        // Get semantically similar notes
        $similarNotes = $this->findSimilarNotes($userInput, $noteLimit);

        return [
            'messages' => $recentMessages,
            'notes' => $similarNotes,
            'mode' => $session->mode,
            'time_of_day' => now()->hour,
        ];
    }

    /**
     * Find semantically similar notes using TF-IDF
     */
    private function findSimilarNotes(string $input, int $limit = 3): array
    {
        $keywords = $this->extractKeywords($input);
        
        if (empty($keywords)) {
            // Prefer recent reflective notes over random
            return Note::whereIn('visibility', ['reflective', 'private'])
                ->latest()
                ->limit($limit)
                ->get()
                ->map(function($note) {
                    return [
                        'title' => $note->title,
                        'excerpt' => $this->getExcerpt($note->body, 150),
                        'type' => $note->type,
                    ];
                })->toArray();
        }

        // TF-IDF based scoring
        $notes = Note::all();
        $scored = [];
        
        foreach ($notes as $note) {
            $score = $this->calculateTfIdfScore($keywords, $note->body ?? '', $note);
            if ($score > 0) {
                $scored[] = [
                    'note' => $note,
                    'score' => $score,
                ];
            }
        }

        // Sort by score descending
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        
        // Take top results
        $topNotes = array_slice($scored, 0, $limit);
        
        return array_map(function($item) {
            return [
                'title' => $item['note']->title,
                'excerpt' => $this->getExcerpt($item['note']->body, 150),
                'type' => $item['note']->type,
            ];
        }, $topNotes);
    }

    /**
     * Calculate TF-IDF score for note relevance
     */
    private function calculateTfIdfScore(array $keywords, string $content, Note $note): float
    {
        $content = strtolower($content);
        $contentWords = str_word_count($content, 1);
        $score = 0;
        
        foreach ($keywords as $keyword) {
            // Term frequency in document
            $tf = substr_count($content, strtolower($keyword)) / max(count($contentWords), 1);
            
            // Inverse document frequency (simplified)
            $idf = log(1 + (Note::count() / max(1, Note::where('body', 'LIKE', "%{$keyword}%")->count())));
            
            $score += $tf * $idf;
        }
        
        // Boost recent notes slightly
        $daysSinceCreation = now()->diffInDays($note->created_at);
        $recencyBoost = 1 / (1 + ($daysSinceCreation / 30)); // Decay over 30 days
        
        // Boost reflective notes
        $visibilityBoost = $note->visibility === 'reflective' ? 1.2 : 1.0;
        
        // Poem vs note context
        $typeBoost = $note->type === 'poem' ? 1.1 : 1.0;
        
        return $score * $recencyBoost * $visibilityBoost * $typeBoost;
    }

    /**
     * Extract keywords from input
     */
    private function extractKeywords(string $input): array
    {
        // Remove common words
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'is', 'am', 'are', 'was', 'were', 'be', 'been', 'being', 'i', 'you', 'he', 'she', 'it', 'we', 'they'];
        
        $words = str_word_count(strtolower($input), 1);
        $keywords = array_diff($words, $stopWords);
        
        return array_slice(array_values($keywords), 0, 5);
    }

    /**
     * Get excerpt from content
     */
    private function getExcerpt(string $content, int $maxLength = 150): string
    {
        $content = strip_tags($content);
        $content = preg_replace('/^#+\s+/m', '', $content); // Remove headers
        $content = trim($content);
        
        if (strlen($content) <= $maxLength) {
            return $content;
        }

        return substr($content, 0, $maxLength) . '...';
    }

    /**
     * Generate response based on mode
     */
    private function generateResponse(string $mode, string $userInput, array $context): ?string
    {
        // Check if AI API is configured
        $openaiKey = env('OPENAI_API_KEY');
        
        if ($openaiKey) {
            return $this->generateAIResponse($mode, $userInput, $context);
        }

        // Fallback to rule-based responses
        return $this->generateRuleBasedResponse($mode, $userInput, $context);
    }

    /**
     * Generate AI-powered response with temporal awareness
     */
    private function generateAIResponse(string $mode, string $userInput, array $context): ?string
    {
        $hour = $context['time_of_day'] ?? now()->hour;
        $systemPrompt = $this->getSystemPrompt($mode, $hour);
        $conversationHistory = $this->formatMessages($context['messages']);
        $noteContext = $this->formatNotes($context['notes']);

        // Adjust parameters based on time
        $isLateNight = $hour >= 23 || $hour <= 3;
        $maxTokens = $mode === 'quiet' ? 50 : 150;
        if ($isLateNight) {
            $maxTokens = (int)($maxTokens * 0.7); // 30% shorter at night
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt . "\n\nContext from your notes:\n" . $noteContext],
                    ...$conversationHistory,
                    ['role' => 'user', 'content' => $userInput],
                ],
                'temperature' => $mode === 'quiet' ? 0.7 : 0.8,
                'max_tokens' => $maxTokens,
            ]);

            if ($response->status() === 200) {
                $data = json_decode($response->body(), true);
                $content = $data['choices'][0]['message']['content'] ?? null;
                return $content ? trim($content) : null;
            }
        } catch (\Exception $e) {
            // Fail gracefully to rule-based or silence
        }

        return $this->generateRuleBasedResponse($mode, $userInput, $context);
    }

    /**
     * Get system prompt for mode with temporal awareness
     */
    private function getSystemPrompt(string $mode, int $hour): string
    {
        $isLateNight = $hour >= 23 || $hour <= 3;
        $isEarlyMorning = $hour >= 4 && $hour <= 6;
        
        $temporalContext = '';
        if ($isLateNight) {
            $temporalContext = " It's late night - be even quieter and briefer. Silence is often best.";
        } elseif ($isEarlyMorning) {
            $temporalContext = " It's early morning - be gentle and minimal.";
        }
        
        if ($mode === 'quiet') {
            return "You are a quiet, reflective presence. Respond very briefly (1-2 sentences max). Ask questions rarely. Often, silence is better than words. Never reference memories or analyze. Never be therapeutic or interpretive. Anchor in the user's own written notes when relevant (unlabeled). No engagement language, no cheerleading." . $temporalContext;
        }

        // company mode
        return "You are a gentle companion. Respond in 2-3 sentences. Ask one open-ended question maximum per response. Mirror the user's tone. Use context from their notes (unlabeled) to create connection. Never reference 'memories' or analyze them. Never be therapeutic. No engagement language. Restraint over capability." . $temporalContext;
    }

    /**
     * Format messages for AI
     */
    private function formatMessages(?\Illuminate\Support\Collection $messages): array
    {
        if (!$messages || $messages->isEmpty()) {
            return [];
        }

        return $messages->map(function($msg) {
            return [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        })->toArray();
    }

    /**
     * Format notes for context (unlabeled)
     */
    private function formatNotes(array $notes): string
    {
        if (empty($notes)) {
            return "(no related notes)";
        }

        $formatted = [];
        foreach ($notes as $note) {
            $formatted[] = $note['excerpt'];
        }

        return implode("\n\n", $formatted);
    }

    /**
     * Generate rule-based response with variation and user's own words
     */
    private function generateRuleBasedResponse(string $mode, string $userInput, array $context): ?string
    {
        $hour = $context['time_of_day'] ?? now()->hour;
        $isLateNight = $hour >= 23 || $hour <= 3;
        
        // In quiet mode, prefer silence (higher at night)
        if ($mode === 'quiet') {
            $silenceChance = $isLateNight ? 2 : 3; // 50% vs 33%
            if (rand(1, $silenceChance) === 1) {
                return null;
            }
        }

        // Check if we have relevant note context
        if (!empty($context['notes'])) {
            $note = $context['notes'][0];
            
            // Try using a short line from their notes as response
            $shortLine = $this->extractMeaningfulLine($note['excerpt']);
            
            if ($shortLine && $mode === 'quiet') {
                // Just reflect their own words back
                return '"' . $shortLine . '"';
            } elseif ($shortLine && $mode === 'company') {
                // Gentle connection using their words
                $responses = [
                    '"' . $shortLine . '"',
                    '"' . $shortLine . '" - does this connect?',
                    'From your notes: "' . $shortLine . '"',
                ];
                return $responses[array_rand($responses)];
            }
            
            // Fallback to excerpt
            if ($mode === 'quiet') {
                return '"' . Str::limit($note['excerpt'], 80) . '"';
            } else {
                return '"' . Str::limit($note['excerpt'], 100) . '" - Does this resonate with what you\'re feeling?';
            }
        }

        // No context - use mirroring or minimal acknowledgment
        if ($mode === 'quiet') {
            // Try to mirror a phrase from their input
            $mirror = $this->extractMirrorPhrase($userInput);
            if ($mirror) {
                return $mirror . '.';
            }
            
            $quietResponses = [
                'I hear you.',
                'Noted.',
                null, // Silence is valid
                'Mm.',
                '...',
            ];
            
            return $quietResponses[array_rand($quietResponses)];
        }

        // Company mode - varied gentle prompts
        $companyResponses = [
            'Tell me more about that.',
            'What does that feel like?',
            'I\'m listening.',
            'Go on.',
            'What else?',
            'And?',
        ];

        return $companyResponses[array_rand($companyResponses)];
    }

    /**
     * Extract a meaningful short line from text
     */
    private function extractMeaningfulLine(string $text): ?string
    {
        $sentences = preg_split('/[.!?]+\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            $wordCount = str_word_count($sentence);
            
            // Perfect short line: 4-12 words, not a question
            if ($wordCount >= 4 && $wordCount <= 12 && !str_ends_with($sentence, '?')) {
                return $sentence;
            }
        }
        
        return null;
    }

    /**
     * Extract a phrase from user input to mirror back
     */
    private function extractMirrorPhrase(string $input): ?string
    {
        // Look for meaningful phrases (not questions)
        if (str_contains($input, '?')) {
            return null;
        }
        
        $words = explode(' ', $input);
        if (count($words) >= 3 && count($words) <= 6) {
            // Take middle portion
            $start = (int)(count($words) / 3);
            $length = min(4, count($words) - $start);
            return implode(' ', array_slice($words, $start, $length));
        }
        
        return null;
    }

    /**
     * Get random line from cache
     */
    public function getRandomLine(): ?string
    {
        return ShortLine::getWeightedRandom();
    }

    /**
     * Generate thoughts summary
     */
    public function generateThoughts(Session $session): ?string
    {
        // Get recent notes
        $recentNotes = Note::latest()->limit(5)->get();
        
        // Get recent session messages
        $recentMessages = $session->getRecentMessages(20);

        if ($recentNotes->isEmpty() && $recentMessages->isEmpty()) {
            return null;
        }

        // For now, simple extraction
        // TODO: Use AI to generate 2-4 line reflective compression
        
        $noteContent = $recentNotes->pluck('content')->implode("\n\n");
        $messageContent = $recentMessages->where('role', 'user')->pluck('content')->implode("\n");
        
        $allContent = trim($noteContent . "\n\n" . $messageContent);
        
        // Extract 2-4 short meaningful sentences
        $sentences = preg_split('/[.!]\s+/', $allContent);
        $meaningful = array_filter($sentences, function($s) {
            return strlen(trim($s)) > 20 && strlen(trim($s)) < 120;
        });

        $selected = array_slice($meaningful, 0, 3);
        
        return implode('. ', $selected) . '.';
    }

    /**
     * Get adjacent notes (similar)
     */
    public function getAdjacentNotes(string $input, int $limit = 3): array
    {
        return $this->findSimilarNotes($input, $limit);
    }
}
