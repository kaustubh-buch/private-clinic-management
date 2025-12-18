<?php

use App\Http\Middleware\Admin\RedirectAuthenticatedAdmin;
use App\Http\Middleware\CellcastAuthorize;
use App\Http\Middleware\Clinic\AccountSuspend;
use App\Http\Middleware\Clinic\EnsureProfileCompletion;
use App\Http\Middleware\Clinic\RedirectAuthenticatedClinic;
use App\Http\Middleware\Clinic\CheckEmailVerification;
use App\Http\Middleware\Clinic\RedirectIfPlanExpire;
use App\Http\Middleware\Clinic\VerifyUser;
use App\Http\Middleware\StripeAuthorize;
use App\Mail\SystemErrorMail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn (Request $request) => route('login'));
        $middleware->alias([
            'front.redirect'=>RedirectAuthenticatedClinic::class,
            'role' => RoleMiddleware::class,
            'admin.redirect' => RedirectAuthenticatedAdmin::class,
            'profile.complete'=>EnsureProfileCompletion::class,
            'email.verification'=> CheckEmailVerification::class,
            'verify.user'=> VerifyUser::class,
            'stripe.authorize' => StripeAuthorize::class,
            'verify.plan' => RedirectIfPlanExpire::class,
            'account.suspend' => AccountSuspend::class,
            'cellcast.authorize' => CellcastAuthorize::class
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('app:send-free-trial-ending-reminder')->dailyAt('09:00');
        $schedule->command('app:cancel-subscriptions')->dailyAt('1:00');
        $schedule->command('app:create-user-subscription')->dailyAt('3:00');
        $schedule->command('app:scheduled-campaign')->everyTenMinutes();
        $schedule->command('app:notify-low-usage-users')->dailyAt('08:00');
        $schedule->command('app:send-free-trial-ending-reminder')->dailyAt('07:00');
        $schedule->command('app:send-failed-payment-reminders')->dailyAt('10:00');
        $schedule->command('app:cleanup-import-temp-files')->daily();
        $schedule->command('app:no-campaign-reminder')->dailyAt('05:00');
        $schedule->command('app:no-import-reminder')->dailyAt('04:00');
        $schedule->command('app:update-clinic-auto-recall-sms')->daily();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
