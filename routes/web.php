<?php

use App\Http\Controllers\Auth\TwoFactorController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;


Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::middleware('dashboard.redirect')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
    Route::post('forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('2fa', [AuthController::class, 'showTwoFactorForm'])->name('2fa.verify');
    Route::resource('patients', PatientController::class);
});

Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::post('2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.otp');
Route::get('2fa/resend', [TwoFactorController::class, 'resendOtp'])->name('2fa.resend');
