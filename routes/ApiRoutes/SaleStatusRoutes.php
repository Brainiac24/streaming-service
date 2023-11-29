<?php

use App\Http\Controllers\SaleStatusController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('sale-statuses')->controller(SaleStatusController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/', 'list');
        Route::get('/{id}', 'findById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
});
