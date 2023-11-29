<?php

use App\Http\Controllers\ChatMessageLikeController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('chats/{chat_id}/messages/{chat_message_id}')->controller(ChatMessageLikeController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/likes', 'list');
        Route::post('/like', 'create');
        Route::delete('/unlike', 'delete');
    });
});
