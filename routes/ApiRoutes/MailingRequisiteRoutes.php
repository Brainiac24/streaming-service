<?php

use App\Http\Controllers\MailingRequisiteController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('mailing-requisites')->controller(MailingRequisiteController::class)->middleware(['auth:api'])->group(function () {
        Route::get('/project/{project_id}', 'list');
        Route::get('/{id}', 'findById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
});
