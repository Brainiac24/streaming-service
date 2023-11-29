<?php

use App\Http\Controllers\EventSessionVisitController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('event-session-visits')->controller(EventSessionVisitController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/stream-stats', 'getStreamStats');
        Route::get('/viewers-stats/export/generate', 'getViewersStats');
        Route::get('/viewers-stats/export', 'downloadViewersStats');
        Route::get('/users-stats/export/generate', 'exportUserList');
        Route::get('/users-stats/export', 'downloadUserList');
    });
});
