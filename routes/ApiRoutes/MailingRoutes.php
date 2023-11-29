<?php

use App\Http\Controllers\MailingController;
use App\Http\Middleware\CheckXAuthTokenMiddleware;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {

    Route::prefix('mailings')->middleware(CheckXAuthTokenMiddleware::class)->controller(MailingController::class)->group(function () {
        Route::put('callback/{uuid}', 'updateCallback');
    });

    Route::prefix('mailings')->controller(MailingController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/{event_id}', 'list');
        Route::get('/{id}', 'findById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
});
