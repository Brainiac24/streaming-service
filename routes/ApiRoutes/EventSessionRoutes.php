<?php

use App\Http\Controllers\EventSessionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('events/link/{event_link}/event-sessions')->controller(EventSessionController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/code/{code}', 'findByCodeForStreamRoom');
    });

    Route::prefix('events/{event_id}/event-sessions')->controller(EventSessionController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/', 'list');
        Route::post('/', 'create');
    });

    Route::prefix('event-sessions')->controller(EventSessionController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/key/{key}', 'findByKeyForStreamRoom');
        Route::put('/{id}/fare/upgrade', 'upgradeFare');
        Route::post('/{id}/logo', 'updateLogoImg');
        Route::delete('/{id}/logo', 'deleteLogoImg');
        Route::get('/{id}/room', 'findByIdForStreamRoom');
        Route::get('/{id}', 'findById');
        Route::put('/{id}', 'update');
    });
});
