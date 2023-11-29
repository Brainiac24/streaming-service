<?php


use App\Http\Controllers\AccessGroupController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('access-groups')->controller(AccessGroupController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/', 'list');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
});
