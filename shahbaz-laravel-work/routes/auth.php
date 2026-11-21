<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login/password', [AuthController::class, 'passwordLogin'])->middleware('throttle:10,1')->name('auth.login.password');
    Route::post('/login/otp/request', [AuthController::class, 'requestLoginOtp'])->middleware('throttle:otp-send')->name('auth.login.otp.request');
    Route::post('/login/otp/verify', [AuthController::class, 'verifyLoginOtp'])->middleware('throttle:otp-verify')->name('auth.login.otp.verify');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register/otp/request', [AuthController::class, 'requestRegisterOtp'])->middleware('throttle:otp-send')->name('auth.register.otp.request');
    Route::post('/register/otp/resend', [AuthController::class, 'resendRegisterOtp'])->middleware('throttle:otp-send')->name('auth.register.otp.resend');
    Route::post('/register/otp/verify', [AuthController::class, 'verifyRegisterOtp'])->middleware('throttle:otp-verify')->name('auth.register.otp.verify');
});

Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');
