<?php

use App\Http\Controllers\ContactGroupController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function ()
{
    Route::prefix('contact-groups')->controller(ContactGroupController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/{id}', 'findById');
        Route::get('/event/{event_id}', 'findByEventId');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
});
