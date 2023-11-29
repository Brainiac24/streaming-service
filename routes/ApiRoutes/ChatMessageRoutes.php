<?php

use App\Http\Controllers\ChatMessageController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('chats/{chat_id}/messages')->controller(ChatMessageController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/', 'listMessages');
        Route::post('/', 'createMessage');
        Route::get('/export/generate', 'exportMessages');
        Route::get('/export', 'downloadMessages');
        Route::put('/{id}/pin', 'pin');
        Route::put('/{id}/unpin', 'unpin');
        Route::put('/{id}/change-to/question', 'changeTypeToQuestion');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });

    Route::prefix('chats/{chat_id}/questions')->controller(ChatMessageController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/', 'listQuestions');
        Route::post('/', 'createQuestion');
        Route::get('/export/generate', 'exportQuestions');
        Route::get('/export', 'downloadQuestions');
        Route::put('/{id}/answered', 'answered');
        Route::put('/{id}/moderated', 'moderated');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });

});
