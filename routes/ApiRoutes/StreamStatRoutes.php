<?php

use App\Http\Controllers\StreamStatController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {

    Route::prefix('stats')->controller(StreamStatController::class)->middleware(['auth:api'])->group(function () {
        //Route::get('/', 'list');
        //Route::get('/{id}', 'findById');
        Route::post('/stream', 'create');
        //Route::put('/{id}', 'update');
        //Route::delete('/{id}', 'delete');
    });
});
