<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminEventController;
use Illuminate\Support\Facades\Route;

Route::middleware(["auth:api", "admin"])->prefix('v1/admin')->group(function () {
    Route::prefix('users')->controller(AdminUserController::class)->group(function () {
        Route::get('/', 'getUserList');
        Route::get('{id}', 'getUser');
        Route::get('{id}/projects', 'getUserProjects');
        Route::get('{id}/events', 'getUserEvents');
        Route::get('{id}/transactions', 'getUserTransactions');
    });
    Route::prefix('event-sessions')->controller(AdminEventController::class)->group(function () {
        Route::get('/', 'getEventSessionList');
    });

    Route::prefix('events')->controller(AdminEventController::class)->group(function () {
        Route::get('/', 'getEventList');
        Route::get('/{id}', 'getEvent');
    });
});
