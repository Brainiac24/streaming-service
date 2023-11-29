<?php

use App\Http\Controllers\MessageTemplateController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function ()
{
    Route::prefix('message-templates')->controller(MessageTemplateController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/', 'list');
        Route::get('/{id}', 'findById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
});
