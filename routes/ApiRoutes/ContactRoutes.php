<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('contacts')->controller(ContactController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/contact-group/{contactGroupId}', 'findByContactGroupId');
        Route::get('/{id}', 'findById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
        Route::post('/import/event/{eventId}', 'uploadContactsFromFile');
        Route::get('/export/generate/event/{eventId}', 'export');
        Route::get('/export/event/{eventId}', 'download');
    });
});
