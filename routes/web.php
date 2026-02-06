<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\ReadController;
use App\Http\Controllers\WriteController;

/**
 * PHASE 2-5: SILENCE-FIRST UI ROUTES
 * 
 * New minimal routes for Write, Read, Adjacent views.
 * Existing API and legacy routes preserved below.
 */

// Phase 2-5: Silence-first UI
Route::get('/write', [WriteController::class, 'create'])->name('write');
Route::post('/notes/store', [WriteController::class, 'store'])->name('notes.store');
Route::get('/notes/{slug}/edit', [WriteController::class, 'edit'])->name('notes.edit');
Route::put('/notes/{slug}', [WriteController::class, 'update'])->name('notes.update');
Route::delete('/notes/{slug}', [WriteController::class, 'destroy'])->name('notes.destroy');
Route::get('/notes/{slug}/download', [ExportController::class, 'downloadNote'])->name('notes.download');
Route::get('/export/vault', [ExportController::class, 'exportVault'])->name('vault.export');
Route::get('/read', [ReadController::class, 'index'])->name('read');
Route::get('/adjacent-view', [ReadController::class, 'adjacent'])->name('adjacent');
Route::get('/view/{type}/{slug}', [ReadController::class, 'show'])
    ->where('type', 'notes|quotes|thoughts')
    ->name('show');

// Existing routes (preserved)
Route::get('/', function () {
    // simple entry that uses the layout and a single input
    return view('welcome');
});

// Mirror lightweight API endpoints on web routes so app servers without
// full provider wiring can still accept requests at /api/*
Route::post('/api/assistant', [AssistantController::class, 'handle'])
    ->withoutMiddleware([
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ]);

Route::post('/api/preview', [PreviewController::class, 'preview']);

Route::post('/api/check-ins', [CheckInController::class, 'store'])
    ->withoutMiddleware([
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    ]);

Route::get('/support', function () {
    return view('support');
});

// Note creation routes (used for first-run blank note)
Route::get('/notes/new', [\App\Http\Controllers\NoteController::class, 'create']);
Route::post('/notes', [\App\Http\Controllers\NoteController::class, 'store']);
Route::get('/export', [\App\Http\Controllers\ExportController::class, 'export']);
Route::get('/notes/{id}', [\App\Http\Controllers\NoteController::class, 'show']);
Route::get('/adjacency', [\App\Http\Controllers\AdjacencyController::class, 'index']);
// Public Waystone
Route::middleware([\App\Http\Middleware\SetPublicMode::class])->group(function () {
    Route::get('/waystone', [\App\Http\Controllers\WaystoneController::class, 'index']);
    Route::get('/philosophy', [\App\Http\Controllers\WaystoneController::class, 'philosophy']);
    Route::get('/download', [\App\Http\Controllers\WaystoneController::class, 'download']);
    Route::get('/silence', [\App\Http\Controllers\WaystoneController::class, 'silence']);
});

// Sit (Mental Health Companion) - minimal, privacy-first
Route::get('/sit', [\App\Http\Controllers\CompanionController::class, 'show']);
Route::post('/sit/begin', [\App\Http\Controllers\CompanionController::class, 'begin']);
Route::post('/sit/check-in', [\App\Http\Controllers\CompanionController::class, 'storeCheckIn']);

// Sharing ritual
// Short-circuit share attempts for non-shareable types to avoid session/middleware
Route::post('/share/checkin/{id}', function () {
    return response('This item cannot be shared.', 403);
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
]);

Route::post('/share/interaction/{id}', function () {
    return response('This item cannot be shared.', 403);
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
]);
Route::get('/share/{type}/{id}/confirm', [\App\Http\Controllers\ShareController::class, 'confirm']);
Route::post('/share/{type}/{id}', [\App\Http\Controllers\ShareController::class, 'makeShareable'])
    ->withoutMiddleware([
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    ]);
