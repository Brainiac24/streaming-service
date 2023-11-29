<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('login/password', 'loginByPassword');
        Route::post('login/otp', 'loginByOTP');
        Route::post('register', 'register');
        Route::post('logout', 'logout');
        Route::get('refresh', 'refreshToken');
        Route::post('logreg', 'checkMustLoginOrRegister');
        Route::get('email/verification/confirm', 'confirmEmail');
        Route::get('otp/resend', 'sendOTP');
        Route::get('register/guest', 'registerGuest');
        Route::get('login/guest', 'loginGuest');
    });

    Route::prefix('auth')->controller(AuthController::class)->middleware("auth:api")->group(function () {
        Route::get('email/verification', 'requestConfirmEmail');
        Route::post('password/change', 'changePassword');
    });
});
