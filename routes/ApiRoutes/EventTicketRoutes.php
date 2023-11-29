<?php

use App\Http\Controllers\EventTicketController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('events/{event_id}/tickets')->controller(EventTicketController::class)->middleware(['auth:api'])->group(function () {
        Route::post('/unique/generate/{count?}', 'generateUniqueTickets');
        Route::post('/multi/generate/{count?}', 'generateMultiTickets');
        Route::get('/unique/export/generate/{status?}', 'exportTickets');
        Route::get('/unique/export/{status?}', 'downloadTickets');
        Route::get('/unique/{status?}', 'listUniqueWithPagination');
        Route::get('/multi/{status?}', 'listMultiWithPagination');
        Route::get('/data', 'getTicketsData');
        Route::post('/find', 'listByTicketsText');
        Route::post('/find/file', 'listByTicketsFile');
    });

    Route::prefix('event-tickets')->controller(EventTicketController::class)->middleware(['auth:api'])->group(function () {
        Route::put('/ban', 'banTickets');
        Route::put('/unban', 'unbanTickets');
        Route::put('/{id}/attach', 'attachTicketToUserIfUserExist');
        Route::put('/{id}/attach/confirm', 'attachTicketToUser');
        Route::put('/{id}/detach', 'detachTicketToUser');
        Route::put('/{id}/ban', 'banTicket');
        Route::put('/{id}/unban', 'unbanTicket');
        Route::put('/{id}/multi', 'updateMultiTicket');
        Route::put('/{id}/unique', 'updateUniqueTicket');
        Route::get('/{id}', 'findByIdForCurrentAuthedUser');
        Route::delete('/{id}', 'delete');
    });
});
