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
     * Send message and get response
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

        // Check for pause/silence requests
        $trimmed = strtolower(trim($content));
        if (in_array($trimmed, ['pause', 'enough', ''])) {
            return null; // Silence is valid
        }

        // Build context
        $context = $this->buildContext($session, $content);

        // Generate response based on mode
        $response = $this->generateResponse($session->mode, $content, $context);

        // Save assistant message (even if null - silence is valid)
        if ($response !== null) {
            Message::create([
                'session_id' => $session->id,
                'role' => 'assistant',
                'content' => $response,
                'created_at' => now(),
            ]);
        }

        return $response;
    }

    /**
     * Build conversation context
     */
    private function buildContext(Session $session, string $userInput): array
    {
        // Get recent messages
        $recentMessages = $session->getRecentMessages(10);

        // Get semantically similar notes (top 3)
        $similarNotes = $this->findSimilarNotes($userInput, 3);

        return [
            'messages' => $recentMessages,
            'notes' => $similarNotes,
        ];
    }

    /**
     * Find semantically similar notes
     * For now, uses simple keyword matching
     * TODO: Implement proper embedding-based similarity
     */
    private function findSimilarNotes(string $input, int $limit = 3): array
    {
        // Simple keyword matching for now
        $keywords = $this->extractKeywords($input);
        
        if (empty($keywords)) {
            return Note::inRandomOrder()->limit($limit)->get()->map(function($note) {
                return [
                    'title' => $note->title,
                    'excerpt' => $this->getExcerpt($note->content, 150),
                ];
            })->toArray();
        }

        $notes = Note::where(function($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $query->orWhere('content', 'LIKE', "%{$keyword}%");
            }
        })
        ->limit($limit)
        ->get();

        return $notes->map(function($note) {
            return [
                'title' => $note->title,
                'excerpt' => $this->getExcerpt($note->content, 150),
            ];
        })->toArray();
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
     * Generate AI-powered response
     */
    private function generateAIResponse(string $mode, string $userInput, array $context): ?string
    {
        $systemPrompt = $this->getSystemPrompt($mode);
        $conversationHistory = $this->formatMessages($context['messages']);
        $noteContext = $this->formatNotes($context['notes']);

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
                'max_tokens' => $mode === 'quiet' ? 50 : 150,
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
     * Get system prompt for mode
     */
    private function getSystemPrompt(string $mode): string
    {
        if ($mode === 'quiet') {
            return "You are a quiet, reflective presence. Respond very briefly (1-2 sentences max). Ask questions rarely. Often, silence is better than words. Never reference memories or analyze. Never be therapeutic or interpretive. Anchor in the user's own written notes when relevant (unlabeled). No engagement language, no cheerleading.";
        }

        // company mode
        return "You are a gentle companion. Respond in 2-3 sentences. Ask one open-ended question maximum per response. Mirror the user's tone. Use context from their notes (unlabeled) to create connection. Never reference 'memories' or analyze them. Never be therapeutic. No engagement language. Restraint over capability.";
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
     * Generate rule-based response (fallback when no AI)
     */
    private function generateRuleBasedResponse(string $mode, string $userInput, array $context): ?string
    {
        // In quiet mode, prefer silence
        if ($mode === 'quiet' && rand(1, 3) === 1) {
            return null; // 33% silence in quiet mode
        }

        // Check if we have relevant note context
        if (!empty($context['notes'])) {
            $note = $context['notes'][0];
            
            if ($mode === 'quiet') {
                // Just reflect the note
                return '"' . Str::limit($note['excerpt'], 80) . '"';
            } else {
                // Company mode - add gentle connection
                return '"' . Str::limit($note['excerpt'], 100) . '" - Does this resonate with what you\'re feeling?';
            }
        }

        // No context, generic responses
        if ($mode === 'quiet') {
            $quietResponses = [
                'I hear you.',
                'Noted.',
                null, // Silence is valid
                'Mm.',
            ];
            
            return $quietResponses[array_rand($quietResponses)];
        }

        // Company mode - gentle prompts
        $companyResponses = [
            'Tell me more about that.',
            'What does that feel like?',
            'I\'m listening.',
            'Go on.',
        ];

        return $companyResponses[array_rand($companyResponses)];
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
