<?php

use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('sales')->controller(SaleController::class)->middleware(['auth:api'])->group(function () {
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');

        Route::put('/{id}/share', 'share');
        Route::put('/{id}/done', 'done');
        Route::put('/{id}/click', 'click');
    });

    Route::prefix('/event-sessions/{event_session_id}')->controller(SaleController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/sales', 'list');
        Route::put('/sales/sort', 'sort');
    });
});
