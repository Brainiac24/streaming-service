<?php

use App\Http\Controllers\Payment\CloudPaymentController;
use App\Http\Controllers\Payment\PaymentRequisiteController;
use App\Http\Controllers\Payment\TinkoffPaymentController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/payments'], function ()
{
    Route::prefix('tinkoff')->controller(TinkoffPaymentController::class)->group(function () {
        Route::post('/notification', 'notificationWebhook');
        Route::post('/init', 'init')->middleware("auth:api");
    });

    Route::prefix('cloudpayments')->controller(CloudPaymentController::class)->group(function () {
        Route::post('/notification/pay', 'payNotification');
        Route::post('/notification/fail', 'failNotification');
        Route::post('/init', 'init')->middleware("auth:api");
    });

    Route::group(['middleware' => 'auth:api'], function ()
    {
        Route::apiResource('requisites', PaymentRequisiteController::class);
    });
});

