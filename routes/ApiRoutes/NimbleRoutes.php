<?php

use App\Http\Controllers\NimbleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function ()
{
    Route::prefix('media')->controller(NimbleController::class)->group(function () {
        Route::post('/publisher_auth', 'publisherAuth');
        Route::post('/publisher_update', 'publisherUpdate');
        Route::post('/publisher_route', 'getPublisherRouteResolution');
        Route::get('/movestream_s3', 'getStreamForS3');
        Route::post('/movestream_s3', 'moveStreamToS3');
    });
});

