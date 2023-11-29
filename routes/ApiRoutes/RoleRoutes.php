<?php


use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function ()
{
    Route::prefix('roles')->controller(RoleController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/', 'list');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
});

