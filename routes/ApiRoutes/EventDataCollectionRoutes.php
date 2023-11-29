<?php

use App\Http\Controllers\EventDataCollectionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {

    Route::prefix('event-data-collections')->controller(EventDataCollectionController::class)->middleware(['auth:api'])->group(function () {
        //Route::get('/', 'list');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        //Route::get('/{id}', 'findById');
        //Route::delete('/{id}', 'delete');
    });
});
