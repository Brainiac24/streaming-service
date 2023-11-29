<?php


use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('permissions')->controller(PermissionController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/', 'list');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
});
