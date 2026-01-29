<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\CheckInController;

Route::get('/status', function () {
    return ['status' => 'ok'];
});

Route::post('/assistant', [AssistantController::class, 'handle']);
Route::post('/check-ins', [CheckInController::class, 'store']);
