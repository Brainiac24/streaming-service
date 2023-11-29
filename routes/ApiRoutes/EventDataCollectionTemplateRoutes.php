<?php

use App\Http\Controllers\EventDataCollectionTemplateController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {

    Route::prefix('events/{event_id}/event-data-collection-templates')->controller(EventDataCollectionTemplateController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/', 'findTemplatesByEventId');
    });


    Route::prefix('event-data-collection-templates')->controller(EventDataCollectionTemplateController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/', 'list');
        Route::get('/{id}', 'findById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
});
