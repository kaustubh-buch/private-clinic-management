<?php

use App\Http\Controllers\Admin\AdminInsuranceController;
use App\Http\Controllers\Clinic\ClinicController;
use App\Http\Controllers\Clinic\InsuranceController;
use App\Http\Controllers\Clinic\TemplateController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
})->name('home');

Route::middleware('front.redirect')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('verify.user')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('profile.complete')->group(function () {
        Route::get('profile', [DashboardController::class, 'profile'])->name('profile');
        Route::resource('notification', NotificationController::class)->only(['index', 'destroy']);

        Route::prefix('notification')->name('notification.')->group(function () {
            Route::post('read-all', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
        });

        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'profile'])->name('profile');
            Route::post('update', [ProfileController::class, 'updateProfile'])->name('profile.update');
            Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change.password');
            Route::name('profile.')->group(function () {
                Route::post('/2fa/request-update-phone', [ProfileController::class, 'requestPhoneUpdate'])->name('request.update.phone');
                Route::post('/2fa/update-phone', [ProfileController::class, 'verifyPhoneUpdateOtp'])->name('update.phone');
            });
        });

        Route::middleware(['verify.plan', 'account.suspend'])->group(function () {
            Route::post('dashboard/quick-recall', [DashboardController::class, 'sendQuickRecallSMS'])->name('dashboard.quick-recall.send');

            Route::prefix('settings')->name('settings.')->group(function () {
                Route::prefix('insurance')->name('insurance.')->group(function () {
                    Route::get('data/{type}', [InsuranceController::class, 'data'])->name('data');
                    Route::post('move', [InsuranceController::class, 'moveCategory'])->name('move');
                    Route::post('update-field', [InsuranceController::class, 'updateField'])->name('update-field');
                    // if want to use resource then put outside
                    Route::get('', [InsuranceController::class, 'index'])->name('index');
                    Route::delete('{insurance}', [InsuranceController::class, 'destroy'])->name('destroy');
                });
            });

            Route::resource('template', TemplateController::class)->except(['create', 'show', 'edit']);
            Route::get('templates/{template}/{category}/default', [TemplateController::class, 'setDefault'])->name('template.setDefault');

        });
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});


Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('admin.redirect')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::prefix('insurance')->name('insurance.')->group(function () {
            Route::prefix('approvals')->name('approvals.')->group(function () {
                Route::get('', [AdminInsuranceApprovalController::class, 'index'])->name('index');
                Route::post('{id}/approve', [AdminInsuranceApprovalController::class, 'approve'])->name('approve');
            });

            Route::resource('/', AdminInsuranceController::class)->parameters(['' => 'insurance']);
        });

    });
});