<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('users')->controller(UserController::class)->middleware("auth:api")->group(function () {
        Route::get('current/data', 'getUserData');
        Route::put('profile/guest', 'updateGuestData');
        Route::put('profile', 'updateUserData');
        Route::post('avatar', 'updateAvatar');
        Route::delete('avatar', 'deleteAvatar');
    });
});
