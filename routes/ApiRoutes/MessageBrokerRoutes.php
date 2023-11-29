<?php

use App\Http\Controllers\MessageBrokerController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('message-broker')->controller(MessageBrokerController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/{uuid}', 'findByBatchId');
        Route::put('/{uuid}/pause', 'pause');
        Route::put('/{uuid}/resume', 'resume');
        Route::put('/{uuid}/message-template', 'updateMessageTemplate');
        Route::put('/{uuid}/smtp-credentials', 'updateSmtpCredentials');
        Route::put('/{uuid}/cancel', 'cancel');
    });
});
