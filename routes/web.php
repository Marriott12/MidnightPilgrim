<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\CheckInController;

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
        \App\Http\Middleware\VerifyCsrfToken::class,
    ]);
