<?php

use App\Http\Controllers\Api\V1\AgentQueueController;
use App\Http\Controllers\Api\V1\PrintJobController;
use Illuminate\Support\Facades\Route;

Route::prefix('print/v1')->group(function (): void {
    Route::middleware('site.api-key')->group(function (): void {
        Route::post('/jobs', [PrintJobController::class, 'store']);
    });

    Route::post('/agents/activate', [AgentQueueController::class, 'activate']);

    Route::middleware('agent.signature')->group(function (): void {
        Route::get('/agents/next', [AgentQueueController::class, 'next']);
        Route::post('/agents/{agentId}/jobs/{jobId}/ack', [AgentQueueController::class, 'ack']);
        Route::post('/agents/{agentId}/jobs/{jobId}/fail', [AgentQueueController::class, 'fail']);
        Route::post('/agents/{agentId}/heartbeat', [AgentQueueController::class, 'heartbeat']);
    });
});
