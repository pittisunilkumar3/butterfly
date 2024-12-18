<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\TwilioWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Twilio Webhook Routes (outside v1 prefix for direct access)
Route::prefix('webhooks/twilio')->group(function () {
    Route::post('message-status', [TwilioWebhookController::class, 'messageStatus']);
    Route::post('incoming-message', [TwilioWebhookController::class, 'incomingMessage']);
});

Route::prefix('v1')->group(function () {
    Route::prefix('leads')->group(function () {
        Route::get('test-whatsapp', [LeadController::class, 'testWhatsApp']);
        Route::get('{id}/conversations', [LeadController::class, 'getConversations']);
        Route::post('{id}/conversations', [LeadController::class, 'sendMessage']);
        Route::get('{id}/conversation-analysis', [LeadController::class, 'getConversationAnalysis']);
    });
});
