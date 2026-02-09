<?php

namespace App\Http\Controllers;

use App\Services\MentalHealthCompanionService;
use Illuminate\Http\Request;

class CompanionController extends Controller
{
    protected MentalHealthCompanionService $companion;

    public function __construct(MentalHealthCompanionService $companion)
    {
        $this->companion = $companion;
    }

    /**
     * Show the Sit page
     */
    public function show()
    {
        return view('sit');
    }

    /**
     * Begin a brief companion moment. Returns a tiny, non-directive phrase.
     * This endpoint intentionally does not store sensitive data.
     */
    public function begin(Request $request)
    {
        // Return a short check-in prompt or reflective line
        $text = $this->companion->checkInPrompt();
        return response()->json(['text' => $text]);
    }

    /**
     * Store optional check-in (always private, stored in companion/)
     * Phase 5: Mental Health Companion - Mode B (brief check-in)
     */
    public function storeCheckIn(Request $request)
    {
        $validated = $request->validate([
            'intensity' => 'required|integer|min:1|max:5',
            'note' => 'nullable|string|max:1000',
        ]);

        // Store as CheckIn (always private, never shareable)
        $checkIn = $this->companion->storeCheckIn(
            mood: 'check-in',
            intensity: $validated['intensity'],
            note: $validated['note'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Saved quietly. Always private.'
        ]);
    }
    
    /**
     * Respond to conversational input (gentle, non-directive)
     * Phase 5: Mental Health Companion - Mode A (reflective conversation)
     */
    public function respond(Request $request)
    {
        $validated = $request->validate([
            'input' => 'required|string|max:2000',
        ]);

        // Get gentle response (references user's own writings or witnessing phrase)
        $response = $this->companion->respondToInputWithReferencePriority($validated['input']);

        return response()->json([
            'text' => $response ?? 'I\'m here.',
            'response' => $response ?? 'I\'m here.'
        ]);
    }
}
