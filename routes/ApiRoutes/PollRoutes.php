<?php

use App\Http\Controllers\PollController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('polls')->controller(PollController::class)->middleware(['auth:api'])->group(function () {
        //Route::get('/{id}', 'findById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');

        Route::put('/{id}/type', 'updateType');
        Route::put('/{id}/start', 'updateStatusToStarted');
        Route::put('/{id}/stop', 'updateStatusToFinished');
        Route::put('/{id}/results/show', 'showResults');
        Route::put('/{id}/results/hide', 'hideResults');
        Route::post('/{id}/vote', 'vote');
    });

    Route::prefix('/event-sessions/{event_session_id}')->controller(PollController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/polls', 'list');
    });
});
