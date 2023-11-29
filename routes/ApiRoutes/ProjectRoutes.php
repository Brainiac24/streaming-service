<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('projects')->controller(ProjectController::class)->group(function () {
        Route::get('/support/event/{link}', 'support');
    });

    Route::prefix('projects')->controller(ProjectController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/', 'list');
        Route::get('/archive', 'archiveList');
        Route::get('/{id}', 'findById');
        Route::get('/{id}/events', 'getEventsByProjectId');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
        Route::put('/{id}/archive', 'archive');
        Route::put('/{id}/revert', 'revert');
    });
});
