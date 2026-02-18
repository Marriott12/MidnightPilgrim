<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\Message;
use App\Services\ConversationalEngineService;
use App\Services\EmotionalPatternEngineService;
use App\Services\NarrativeContinuityEngineService;
use App\Services\DisciplineService;
use App\Services\DisciplineContractService;
use App\Services\DisciplineNotificationService;
use App\Services\PatternTrackingService;
use App\Services\FeatureButtonService;
use App\Services\SessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\DeclarePlatformRequest;

/**
 * AdaptiveConversationController
 * 
 * Handles all endpoints for the psychologically adaptive conversational system
 */
class AdaptiveConversationController extends Controller
{
    public function __construct(
        private SessionService $sessionService,
        private DisciplineService $disciplineService,
        private ConversationalEngineService $conversationalEngine,
        private EmotionalPatternEngineService $patternEngine,
        private NarrativeContinuityEngineService $narrativeEngine,
        private DisciplineContractService $disciplineContract,
        private PatternTrackingService $patternTracking,
        private FeatureButtonService $featureButtons,
        private DisciplineNotificationService $notifications
    ) {}

    /**
     * Initialize or resume a session
     * 
     * POST /api/conversation/init
     * Body: { mode: 'quiet' | 'company' }
     */
    public function initSession(Request $request)
    {
        $request->validate([
            'mode' => 'sometimes|in:quiet,company',
        ]);

        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->sessionService->generateFingerprint($ip, $userAgent);
        $profile = $this->sessionService->findOrCreateProfile($fingerprint);
        $activeSession = $this->sessionService->findActiveSession($fingerprint);

        if ($activeSession) {
            $resumePrompt = $this->conversationalEngine->generateResumePrompt($activeSession);
            return response()->json([
                'session_uuid' => $activeSession->uuid,
                'mode' => $activeSession->mode,
                'has_active_session' => true,
                'resume_prompt' => $resumePrompt,
                'message_count' => $activeSession->message_count,
            ]);
        }

        $mode = $request->input('mode', $profile->preferred_mode);
        $session = $this->sessionService->createSession($profile, $fingerprint, $mode);
        $this->sessionService->incrementSessionCounter($profile);

        return response()->json([
            'session_uuid' => $session->uuid,
            'mode' => $session->mode,
            'has_active_session' => false,
            'message_count' => 0,
        ]);
    }

    /**
     * Handle session resume/new decision
     * 
     * POST /api/conversation/resume-decision
     * Body: { session_uuid, action: 'resume' | 'new' }
     */
    public function resumeDecision(Request $request)
    {
        $request->validate([
            'session_uuid' => 'required|uuid',
            'action' => 'required|in:resume,new',
        ]);

        $session = Session::where('uuid', $request->session_uuid)->firstOrFail();
        $profile = $session->userProfile;

        if ($request->action === 'new') {
            $this->sessionService->closeSession($session);
            $newSession = $this->sessionService->createSession(
                $profile,
                $session->fingerprint,
                $request->input('mode', $profile->preferred_mode)
            );
            $this->sessionService->incrementSessionCounter($profile);
            return response()->json([
                'session_uuid' => $newSession->uuid,
                'mode' => $newSession->mode,
                'message_count' => 0,
            ]);
        }
        // Resume existing session
        $this->sessionService->resumeSession($session);
        return response()->json([
            'session_uuid' => $session->uuid,
            'mode' => $session->mode,
            'message_count' => $session->message_count,
        ]);
    }

    /**
     * Declare platform and create contract
     *
     * POST /api/discipline/declare-platform
     * Body: { platform, timezone, start_date? }
     */
    public function declarePlatform(DeclarePlatformRequest $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->sessionService->generateFingerprint($ip, $userAgent);
        $profile = $this->sessionService->findOrCreateProfile($fingerprint);

        $request->validate([
            'platform' => 'required|string|max:255',
            'timezone' => 'required|timezone',
            'start_date' => 'sometimes|date|after_or_equal:today|before_or_equal:' . now()->addDays(7)->format('Y-m-d'),
        ]);

        // Check if platform already declared
        if ($profile->hasDeclaredPlatform()) {
            return response()->json([
                'success' => false,
                'error' => 'Platform already declared and locked.',
                'declared_platform' => $profile->declared_platform,
                'declared_at' => $profile->platform_declared_at->toIso8601String(),
            ], 403);
        }

        try {
            // Declare platform (locks it forever)
            $profile->declarePlatform($request->platform, $request->timezone);

            // Auto-create discipline contract
            $startDate = $request->has('start_date') 
                ? Carbon::parse($request->start_date)
                : Carbon::now()->addDays(7); // Default: start in 7 days

            $contract = $this->disciplineService->initializeContract($profile, $request->timezone, $startDate);

            return response()->json([
                'success' => true,
                'message' => 'Platform declared and contract created. This is now binding.',
                'platform' => $profile->declared_platform,
                'platform_locked' => true,
                'contract' => [
                    'id' => $contract->id,
                    'start_date' => $contract->start_date instanceof \Carbon\Carbon ? $contract->start_date->toDateString() : \Carbon\Carbon::parse($contract->start_date)->toDateString(),
                    'end_date' => $contract->end_date instanceof \Carbon\Carbon ? $contract->end_date->toDateString() : \Carbon\Carbon::parse($contract->end_date)->toDateString(),
                    'total_weeks' => $contract->total_weeks,
                    'status' => $contract->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send a message and get response
     * 
     * POST /api/conversation/message
     * Body: { session_uuid, message, mode? }
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'session_uuid' => 'required|uuid',
            'message' => 'required|string|max:5000',
            'mode' => 'sometimes|in:quiet,company',
        ]);

        $session = Session::where('uuid', $request->session_uuid)->firstOrFail();

        if (!$session->isActive()) {
            return response()->json(['error' => 'Session is closed'], 400);
        }

        // Save user message
        Message::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $request->message,
            'created_at' => now(),
        ]);

        // Analyze message for discipline patterns (vagueness, avoidance, etc.)
        $this->conversationalEngine->analyzeMessage($request->message, $session);

        // Analyze message for emotional patterns
        $analysis = $this->patternEngine->analyzeMessage($request->message);

        // Update session metrics
        $this->patternEngine->updateSessionMetrics($session, $analysis);

        // Use escalation tone if detected
        $tone = $session->escalation_tone === 'sharp' ? 'sharp' :
        // Get user profile for tone adaptation
        $profile = $session->userProfile;
        $tone = $this->conversationalEngine->adaptTone($profile);

        // Update mode if changed
        if ($request->has('mode') && $request->mode !== $session->mode) {
            $session->mode = $request->mode;
            $session->save();
        }

        // Generate system prompt
        $systemPrompt = $this->conversationalEngine->getSystemPrompt($session->mode, $profile, $tone);

        // Get recent messages for context
        $recentMessages = $session->getRecentMessages(10);
        $contextMessages = $recentMessages->map(function ($msg) {
            return [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        })->toArray();

        // Call AI service (placeholder - integrate with your AI service)
        $assistantResponse = $this->generateAIResponse($systemPrompt, $contextMessages);

        // Format response based on mode
        $formattedResponse = $this->conversationalEngine->formatResponse($assistantResponse, $session->mode);

        // Save assistant message
        Message::create([
            'session_id' => $session->id,
            'role' => 'assistant',
            'content' => $formattedResponse,
            'created_at' => now(),
        ]);

        // Calculate response delay
        $responseDelay = $this->conversationalEngine->calculateResponseDelay($session->session_intensity);

        return response()->json([
            'message' => $formattedResponse,
            'delay' => $responseDelay,
            'intensity' => $session->session_intensity,
            'tone_adaptation' => $tone,
        ]);
    }

    /**
     * End session and create emotional snapshot
     * 
     * POST /api/conversation/end
     * Body: { session_uuid }
     */
    public function endSession(Request $request)
    {
        $request->validate([
            'session_uuid' => 'required|uuid',
        ]);
        $session = Session::where('uuid', $request->session_uuid)->firstOrFail();
        $profile = $session->userProfile;
        $result = $this->sessionService->endSession($session, $profile, $this->patternEngine, $this->narrativeEngine);
        return response()->json($result);
    }

    /**
     * Delete current session permanently
     * 
     * DELETE /api/conversation/session
     * Body: { session_uuid }
     */
    public function deleteSession(Request $request)
    {
        $request->validate([
            'session_uuid' => 'required|uuid',
        ]);
        $session = Session::where('uuid', $request->session_uuid)->firstOrFail();
        $this->sessionService->deleteSession($session);
        return response()->json(['success' => true]);
    }

    /**
     * Delete all user data
     * 
     * DELETE /api/conversation/profile
     */
    public function deleteProfile(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->sessionService->generateFingerprint($ip, $userAgent);
        $this->sessionService->deleteUserProfile($fingerprint);
        return response()->json(['success' => true]);
    }

    /**
     * Get random philosophical prompt
     * 
     * GET /api/conversation/random-prompt
     */
    public function getRandomPrompt(Request $request)
    {
        $challenge = $this->featureButtons->generateRandomChallenge();

        return response()->json($challenge);
    }

    /**
     * Get thoughts/reflection for current session
     * 
     * GET /api/conversation/thoughts
     * Query: session_uuid
     */
    public function getThoughts(Request $request)
    {
        $thoughtPrompt = $this->featureButtons->generateThoughtPrompt();

        return response()->json($thoughtPrompt);
    }

    /**
     * Get adjacent theme suggestion
     * 
     * GET /api/conversation/adjacent
     */
    public function getAdjacentTheme(Request $request)
    {
        $request->validate([
            'session_uuid' => 'sometimes|uuid',
        ]);

        if ($request->has('session_uuid')) {
            $session = Session::where('uuid', $request->session_uuid)->first();
            if ($session) {
                $theme = $this->featureButtons->generateAdjacentTheme($session);
                return response()->json($theme);
            }
        }

        // No session context, provide default reframe
        return response()->json([
            'type' => 'reframe',
            'title' => 'Perspective Shift',
            'content' => 'What would change if you approached this with fresh assumptions?',
        ]);
    }

    /**
     * Get narrative reflection (every 5 sessions)
     * 
     * GET /api/conversation/reflection
     */
    public function getReflection(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->conversationalEngine->generateFingerprint($ip, $userAgent);

        $profile = $this->conversationalEngine->findOrCreateProfile($fingerprint);
        $reflection = $this->narrativeEngine->getLatestReflection($profile);

        if (!$reflection) {
            return response()->json(['has_reflection' => false]);
        }

        // Mark as shown
        $reflection->markAsShown();

        return response()->json([
            'has_reflection' => true,
            'observations' => $reflection->pattern_observations,
            'contradiction' => $reflection->identified_contradiction,
            'question' => $reflection->philosophical_question,
        ]);
    }

    /**
     * Update preferred mode
     * 
     * POST /api/conversation/update-mode
     * Body: { mode }
     */
    public function updatePreferredMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:quiet,company',
        ]);

        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->conversationalEngine->generateFingerprint($ip, $userAgent);

        $profile = $this->conversationalEngine->findOrCreateProfile($fingerprint);
        $profile->update(['preferred_mode' => $request->mode]);

        return response()->json(['success' => true]);
    }

    /**
     * Generate AI response (placeholder - integrate with your AI service)
     * 
     * This should call OpenAI, Claude, or your chosen LLM
     */
    private function generateAIResponse(string $systemPrompt, array $messages): string
    {
        // Integrate with OpenAI GPT-4 if API key is set, else fallback
        $apiKey = env('OPENAI_API_KEY');
        if ($apiKey) {
            try {
                $client = new \GuzzleHttp\Client([
                    'base_uri' => 'https://api.openai.com/v1/',
                    'timeout'  => 15.0,
                ]);
                $response = $client->post('chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => 'gpt-4',
                        'messages' => array_merge([
                            ['role' => 'system', 'content' => $systemPrompt]
                        ], $messages),
                        'temperature' => 0.8,
                        'max_tokens' => 500,
                    ],
                ]);
                $data = json_decode($response->getBody(), true);
                if (isset($data['choices'][0]['message']['content'])) {
                    return trim($data['choices'][0]['message']['content']);
                }
            } catch (\Throwable $e) {
                Log::error('AI API error: ' . $e->getMessage(), \App\Support\LogSanitizer::sanitize([
                    'systemPrompt' => $systemPrompt,
                    'messages' => $messages,
                ]));
                // Fallback below
            }
        }
        // Fallback: rule-based or static response
        return "(AI unavailable) What would you say to yourself if you were listening with compassion?";
    }

    // ==========================================
    // DISCIPLINE CONTRACT ENDPOINTS
    // ==========================================

    /**
     * Check if user has declared platform (required for all discipline features)
     */
    private function requirePlatformDeclaration($profile)
    {
        if (!$profile->hasDeclaredPlatform()) {
            return response()->json([
                'success' => false,
                'error' => 'Platform declaration required',
                'message' => 'You must declare your writing platform before using discipline features.',
                'action' => 'declare_platform',
            ], 403);
        }
        return null;
    }

    /**
     * Initialize discipline contract (DEPRECATED - use declare-platform instead)
     * 
     * POST /api/discipline/init
     */
    public function initDisciplineContract(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->conversationalEngine->generateFingerprint($ip, $userAgent);
        $profile = $this->conversationalEngine->findOrCreateProfile($fingerprint);

        // Platform check
        if ($check = $this->requirePlatformDeclaration($profile)) {
            return $check;
        }

        // Check if contract already exists
        $existingContract = $profile->activeDisciplineContract();
        if ($existingContract) {
            return response()->json([
                'success' => false,
                'message' => 'Active discipline contract already exists.',
            ], 400);
        }

        $contract = $this->disciplineService->initializeContract($profile);

        return response()->json([
            'success' => true,
            'contract' => [
                'start_date' => $contract->start_date instanceof \Carbon\Carbon ? $contract->start_date->toDateString() : \Carbon\Carbon::parse($contract->start_date)->toDateString(),
                'end_date' => $contract->end_date instanceof \Carbon\Carbon ? $contract->end_date->toDateString() : \Carbon\Carbon::parse($contract->end_date)->toDateString(),
                'total_weeks' => $contract->total_weeks,
            ],
        ]);
    }

    /**
     * Get discipline contract status
     * 
     * GET /api/discipline/status
     */
    public function getDisciplineStatus(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->sessionService->generateFingerprint($ip, $userAgent);
        $profile = $this->sessionService->findOrCreateProfile($fingerprint);
        $status = $this->disciplineService->getContractStatus($profile);
        return response()->json($status);
    }

    /**
     * Submit poem
     * 
     * POST /api/discipline/submit-poem
     * Body: { content, self_assessment: { lazy_where, abstraction_where, weakest_line, risk_avoided } }
     */
    public function submitPoem(Request $request)
    {
        $request->validate([
            'content' => 'required|string|min:50',
            'self_assessment' => 'required|array',
            'self_assessment.lazy_where' => 'required|string|min:20',
            'self_assessment.abstraction_where' => 'required|string|min:20',
            'self_assessment.weakest_line' => 'required|string|min:20',
            'self_assessment.risk_avoided' => 'required|string|min:20',
        ], [
            'self_assessment.*.min' => 'Self-assessment responses must be at least 20 characters. Be specific.',
        ]);
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->sessionService->generateFingerprint($ip, $userAgent);
        $profile = $this->sessionService->findOrCreateProfile($fingerprint);
        $result = $this->disciplineService->submitPoem(
            $profile,
            $request->input('content'),
            $request->self_assessment
        );
        return response()->json($result);
    }

    /**
     * Publish poem for monthly release
     * 
     * POST /api/discipline/publish-poem
     * Body: { poem_id, platform }
     */
    public function publishPoem(Request $request)
    {
        $request->validate([
            'poem_id' => 'required|integer',
            'platform' => 'required|string',
        ]);

        $poem = \App\Models\Poem::findOrFail($request->poem_id);
        $result = $this->disciplineService->publishPoem($poem, $request->platform);

        return response()->json($result);
    }

    /**
     * Get pattern reports
     * 
     * GET /api/discipline/patterns
     */
    public function getPatternReports(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->conversationalEngine->generateFingerprint($ip, $userAgent);
        $profile = $this->conversationalEngine->findOrCreateProfile($fingerprint);

        $patterns = $this->patternTracking->getUnacknowledgedPatterns($profile);

        return response()->json([
            'has_patterns' => $patterns->isNotEmpty(),
            'patterns' => $patterns->map(fn($p) => [
                'id' => $p->id,
                'type' => $p->pattern_type,
                'description' => $p->description,
                'evidence' => $p->evidence,
                'correction_strategy' => $p->correction_strategy,
                'specific_exercise' => $p->specific_exercise,
            ]),
        ]);
    }

    /**
     * Acknowledge pattern report
     * 
     * POST /api/discipline/acknowledge-pattern
     * Body: { pattern_id }
     */
    public function acknowledgePattern(Request $request)
    {
        $request->validate([
            'pattern_id' => 'required|integer',
        ]);

        $pattern = \App\Models\PatternReport::findOrFail($request->pattern_id);
        $pattern->acknowledge();

        return response()->json(['success' => true]);
    }

    /**
     * Generate pattern report summary
     * 
     * GET /api/discipline/pattern-summary
     */
    public function getPatternSummary(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->conversationalEngine->generateFingerprint($ip, $userAgent);
        $profile = $this->conversationalEngine->findOrCreateProfile($fingerprint);

        $summary = $this->patternTracking->generatePatternReportSummary($profile);

        return response()->json(['summary' => $summary]);
    }

    /**
     * Submit poem revision
     * 
     * POST /api/discipline/submit-revision
     * Body: { poem_id, content, revision_notes, version_number }
     */
    public function submitRevision(Request $request)
    {
        $request->validate([
            'poem_id' => 'required|integer',
            'content' => 'required|string|min:50',
            'revision_notes' => 'required|string|min:20',
            'version_number' => 'required|integer|min:2',
        ]);

        $poem = \App\Models\Poem::findOrFail($request->poem_id);
        $profile = $poem->userProfile;

        $result = $this->disciplineService->submitPoem(
            $profile,
            $request->input('content'),
            $poem->self_assessment ?? [],
            $request->revision_notes,
            $request->version_number
        );

        if ($result['success']) {
            $poem->revision_notes = $request->revision_notes;
            $poem->save();
        }

        return response()->json($result);
    }

    /**
     * Get compliance log dashboard
     * 
     * GET /api/discipline/compliance-log
     */
    public function getComplianceLog(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->conversationalEngine->generateFingerprint($ip, $userAgent);
        $profile = $this->conversationalEngine->findOrCreateProfile($fingerprint);

        $contract = $profile->activeDisciplineContract();

        if (!$contract) {
            return response()->json([
                'active' => false,
                'message' => 'No active discipline contract.',
            ]);
        }

        $logs = $this->disciplineContract->getComplianceLog($contract);

        return response()->json([
            'active' => true,
            'logs' => $logs,
            'contract_start' => $contract->start_date instanceof \Carbon\Carbon ? $contract->start_date->toIso8601String() : \Carbon\Carbon::parse($contract->start_date)->toIso8601String(),
            'contract_end' => $contract->end_date instanceof \Carbon\Carbon ? $contract->end_date->toIso8601String() : \Carbon\Carbon::parse($contract->end_date)->toIso8601String(),
            'current_week' => $contract->getCurrentWeekNumber(),
            'total_weeks' => $contract->total_weeks,
        ]);
    }

    /**
     * Upload recording for monthly release
     * 
     * POST /api/discipline/upload-recording
     * Body: { poem_id, recording: file }
     */
    public function uploadRecording(Request $request)
    {
        $request->validate([
            'poem_id' => 'required|integer',
            'recording' => 'required|file|mimes:mp3,wav,m4a|max:20480', // 20MB max
        ]);

        $poem = \App\Models\Poem::findOrFail($request->poem_id);

        if (!$request->hasFile('recording')) {
            return response()->json(['error' => 'No recording file uploaded'], 400);
        }

        $file = $request->file('recording');
        $filename = 'recording_week_' . $poem->week_number . '_' . time() . '.' . $file->extension();
        $path = $file->storeAs('recordings/' . $poem->userProfile->id, $filename, 'local');

        $poem->recording_file_path = $path;
        $poem->save();

        return response()->json([
            'success' => true,
            'path' => $path,
            'message' => 'Recording uploaded successfully.',
        ]);
    }

    /**
     * Complete weekly reflection
     * 
     * POST /api/discipline/complete-reflection
     * Body: { week_number, content }
     */
    public function completeReflection(Request $request)
    {
        $request->validate([
            'week_number' => 'required|integer|min:1',
            'content' => 'required|string|min:100',
        ]);

        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->conversationalEngine->generateFingerprint($ip, $userAgent);
        $profile = $this->conversationalEngine->findOrCreateProfile($fingerprint);

        $contract = $profile->activeDisciplineContract();

        if (!$contract) {
            return response()->json(['error' => 'No active contract'], 400);
        }

        $poem = \App\Models\Poem::where('user_profile_id', $profile->id)
            ->where('week_number', $request->week_number)
            ->first();

        if (!$poem) {
            return response()->json(['error' => 'No poem found for this week'], 404);
        }

        $poem->reflection_completed = true;
        $poem->save();

        // Update compliance log
        $complianceLog = $contract->complianceLogs()
            ->where('week_number', $request->week_number)
            ->first();

        if ($complianceLog instanceof \App\Models\ComplianceLog) {
            $complianceLog->save();
        }

        // Save reflection to archive
        try {
            $archiveService = app(\App\Services\ArchiveEnforcementService::class);
            $archiveService->createReflection($contract, $request->input('week_number'), $request->input('content'));

            return response()->json([
                'success' => true,
                'message' => "Week {$request->week_number} reflection saved.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to save reflection: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get discipline notifications
     * 
     * GET /api/discipline/notifications
     */
    public function getNotifications(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = $this->conversationalEngine->generateFingerprint($ip, $userAgent);
        $profile = $this->conversationalEngine->findOrCreateProfile($fingerprint);

        $summary = $this->notifications->getNotificationSummary($profile);

        return response()->json($summary);
    }
}
