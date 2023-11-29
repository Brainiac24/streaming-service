<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function ()
{
    Route::prefix('transactions')->controller(TransactionController::class)->middleware("auth:api")->group(function () {
        Route::get('/', 'list');
    });
});

