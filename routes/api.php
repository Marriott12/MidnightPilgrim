<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\AdaptiveConversationController;

Route::get('/status', function () {
    return ['status' => 'ok'];
});

Route::post('/assistant', [AssistantController::class, 'handle']);
Route::post('/check-ins', [CheckInController::class, 'store']);

// Adaptive Conversation System Routes
Route::prefix('conversation')->group(function () {
    // Session management
    Route::post('/init', [AdaptiveConversationController::class, 'initSession']);
    Route::post('/resume-decision', [AdaptiveConversationController::class, 'resumeDecision']);
    Route::post('/message', [AdaptiveConversationController::class, 'sendMessage']);
    Route::post('/end', [AdaptiveConversationController::class, 'endSession']);
    
    // Data deletion
    Route::delete('/session', [AdaptiveConversationController::class, 'deleteSession']);
    Route::delete('/profile', [AdaptiveConversationController::class, 'deleteProfile']);
    
    // Feature buttons
    Route::get('/random-prompt', [AdaptiveConversationController::class, 'getRandomPrompt']);
    Route::get('/thoughts', [AdaptiveConversationController::class, 'getThoughts']);
    Route::get('/adjacent', [AdaptiveConversationController::class, 'getAdjacentTheme']);
    Route::get('/reflection', [AdaptiveConversationController::class, 'getReflection']);
    
    // Settings
    Route::post('/update-mode', [AdaptiveConversationController::class, 'updatePreferredMode']);
});

// Discipline Contract Routes
Route::prefix('discipline')->group(function () {
    // Platform declaration (unprotected - required first step)
    Route::post('/declare-platform', [AdaptiveConversationController::class, 'declarePlatform']);
    
    // Contract management
    Route::post('/init', [AdaptiveConversationController::class, 'initDisciplineContract']);
    Route::get('/status', [AdaptiveConversationController::class, 'getDisciplineStatus']);
    Route::get('/compliance-log', [AdaptiveConversationController::class, 'getComplianceLog']);
    Route::get('/notifications', [AdaptiveConversationController::class, 'getNotifications']);
    
    // Poetry submission and revision
    Route::post('/submit-poem', [AdaptiveConversationController::class, 'submitPoem']);
    Route::post('/submit-revision', [AdaptiveConversationController::class, 'submitRevision']);
    Route::post('/publish-poem', [AdaptiveConversationController::class, 'publishPoem']);
    Route::post('/upload-recording', [AdaptiveConversationController::class, 'uploadRecording']);
    Route::post('/complete-reflection', [AdaptiveConversationController::class, 'completeReflection']);
    
    // Pattern tracking
    Route::get('/patterns', [AdaptiveConversationController::class, 'getPatternReports']);
    Route::post('/acknowledge-pattern', [AdaptiveConversationController::class, 'acknowledgePattern']);
    Route::get('/pattern-summary', [AdaptiveConversationController::class, 'getPatternSummary']);
});

