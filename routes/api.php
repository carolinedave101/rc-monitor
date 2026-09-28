<?php

use App\Http\Controllers\Api\AgentController;
use Illuminate\Support\Facades\Route;

Route::middleware('agent')->group(function () {
    Route::post('/agent/heartbeat', [AgentController::class, 'heartbeat']);
    Route::post('/agent/ingest', [AgentController::class, 'ingest']);
});