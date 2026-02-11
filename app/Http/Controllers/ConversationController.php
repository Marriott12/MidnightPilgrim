<?php

namespace App\Http\Controllers;

use App\Services\ConversationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * ConversationController - SILENCE-FIRST CONVERSATION INTERFACE
 * 
 * Handles conversation flow with anonymous UUID-based identity.
 * NO user accounts, NO IP tracking, NO analytics.
 */
class ConversationController extends Controller
{
    protected ConversationService $conversation;

    public function __construct(ConversationService $conversation)
    {
        $this->conversation = $conversation;
    }

    /**
     * Show conversation interface
     */
    public function index(Request $request)
    {
        $uuid = $this->getOrCreateUUID($request);
        $hasActiveSession = $this->conversation->hasActiveSession($uuid);
        $session = null;

        if ($hasActiveSession) {
            $session = $this->conversation->getActiveSession($uuid);
        }

        $response = response()->view('conversation.index', [
            'hasActiveSession' => $hasActiveSession,
            'session' => $session,
            'messages' => $session ? $session->getRecentMessages(50) : collect([]),
        ]);

        // Set UUID cookie if new
        if (!$request->cookie('pilgrim_uuid')) {
            $response->cookie('pilgrim_uuid', $uuid, 525600, '/', null, true, true);
        }

        return $response;
    }

    /**
     * Resume existing session
     */
    public function resume(Request $request)
    {
        $uuid = $this->getOrCreateUUID($request);
        $session = $this->conversation->getOrCreateSession($uuid, $request->input('mode', 'quiet'));

        return redirect()->route('conversation.index');
    }

    /**
     * Begin new session (delete old)
     */
    public function beginNew(Request $request)
    {
        $uuid = $this->getOrCreateUUID($request);
        $mode = $request->input('mode', 'quiet');
        
        $session = $this->conversation->beginAgain($uuid, $mode);

        return redirect()->route('conversation.index');
    }

    /**
     * Send message
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'mode' => 'sometimes|in:quiet,company',
        ]);

        $uuid = $this->getOrCreateUUID($request);
        $mode = $request->input('mode', 'quiet');
        
        $session = $this->conversation->getOrCreateSession($uuid, $mode);
        
        // Update session mode if changed
        if ($session->mode !== $mode) {
            $session->update(['mode' => $mode]);
        }

        $response = $this->conversation->sendMessage($session, $request->input('message'));

        return response()->json([
            'response' => $response,
            'silence' => $response === null,
        ]);
    }

    /**
     * Get random line
     */
    public function random(Request $request)
    {
        $line = $this->conversation->getRandomLine();

        return response()->json([
            'line' => $line,
        ]);
    }

    /**
     * Get thoughts summary
     */
    public function thoughts(Request $request)
    {
        $uuid = $this->getOrCreateUUID($request);
        $session = $this->conversation->getActiveSession($uuid);

        if (!$session) {
            return response()->json([
                'thoughts' => null,
                'error' => 'No active session',
            ], 404);
        }

        $thoughts = $this->conversation->generateThoughts($session);

        return response()->json([
            'thoughts' => $thoughts,
        ]);
    }

    /**
     * Get adjacent notes
     */
    public function adjacent(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:500',
        ]);

        $notes = $this->conversation->getAdjacentNotes($request->input('query'), 3);

        return response()->json([
            'notes' => $notes,
        ]);
    }

    /**
     * Get or create UUID from cookie
     */
    private function getOrCreateUUID(Request $request): string
    {
        $uuid = $request->cookie('pilgrim_uuid');

        if (!$uuid) {
            $uuid = (string) Str::uuid();
        }

        return $uuid;
    }

    /**
     * Change mode
     */
    public function changeMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:quiet,company',
        ]);

        $uuid = $this->getOrCreateUUID($request);
        $session = $this->conversation->getActiveSession($uuid);

        if ($session) {
            $session->update(['mode' => $request->input('mode')]);
        }

        return response()->json([
            'mode' => $request->input('mode'),
        ]);
    }

    /**
     * Close session
     */
    public function close(Request $request)
    {
        $uuid = $this->getOrCreateUUID($request);
        $session = $this->conversation->getActiveSession($uuid);

        if ($session) {
            $session->close();
        }

        return response()->json([
            'closed' => true,
        ]);
    }
}
