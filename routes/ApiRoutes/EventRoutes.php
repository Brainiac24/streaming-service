<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {

    Route::prefix('events')->controller(EventController::class)->group(function () {
        Route::get('/reception/{link}', 'reception');
    });

    Route::prefix('events')->controller(EventController::class)->middleware(['auth:api'])->group(function () {

        Route::get('/upcoming', 'upcoming');
        Route::get('/archive', 'archive');
        Route::get('/{id}', 'findById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::post('/{id}/enter', 'enter');
        Route::post('/{id}/cover', 'updateCoverImg');
        Route::delete('/{id}/cover', 'deleteCoverImg');
        Route::post('/{id}/logo', 'updateLogoImg');
        Route::delete('/{id}/logo', 'deleteLogoImg');
        Route::post('/{id}/action/redirect', 'redirect');
    });
});
