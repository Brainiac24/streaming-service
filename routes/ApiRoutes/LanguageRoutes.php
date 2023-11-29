<?php

use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function ()
{
    Route::prefix('lang')->controller(LanguageController::class)->middleware("auth:api")->group(function () {
        Route::get('{lang?}', 'getLang');
    });
});

