<?php

use App\Http\Controllers\EventAccessController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {

    Route::prefix('events/{event_id}/event-accesses')->controller(EventAccessController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/current-user', 'getCurrentUserRolesToEvent');
    });

    Route::prefix('events/{event_id}/event-accesses')->controller(EventAccessController::class)->middleware(['auth:api'])->group(function () {
        Route::post('/attach/role', 'attachRoleToEventIfUserExist');
        Route::post('/attach/role/confirm', 'attachRoleToEvent');
        Route::put('/detach/role', 'detachRoleToEvent');
        Route::get('/{role_name?}', 'getUsersWithRolesToEvent');
    });

   
});
