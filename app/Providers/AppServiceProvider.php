<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\Patient;
use App\Models\PaymentMethod;
use App\Models\SubscriptionPlan;
use App\Models\Template;
use App\Models\User;
use App\Observers\MessageObserver;
use App\Observers\PatientObserver;
use App\Observers\SubscriptionPlanObserver;
use App\Observers\TemplateObserver;
use App\Policies\PaymentMethodPolicy;
use App\Policies\TemplatePolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route(
                $notifiable->hasRole('doctor') ? 'password.reset' : 'admin.password.reset',
                ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()],
                false
            ));

            if ($notifiable->hasRole('doctor')) {
                return (new MailMessage)
                    ->subject(__('messages.labels.reset_password_email_title'))
                    ->view('clinic.pages.email-template.reset-password-email', [
                        'resetUrl' => $url,
                        'user' => $notifiable,
                    ]);
            }

            // Use default view for others (Laravel's default message style)
            return (new MailMessage)
                ->subject(__('Reset Password Notification'))
                ->line('You are receiving this email because we received a password reset request for your account.')
                ->action('Reset Password', $url)
                ->line('If you did not request a password reset, no further action is required.');
        });

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $params = ['token' => $token, 'email' => $user->email];

            if ($user->hasRole('doctor')) {
                return route('password.reset', $params);
            }

            return route('admin.password.reset', $params);
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->view('clinic.pages.email-template.verify-email', [
                    'verificationUrl' => $url,
                    'user' => $notifiable,
                ])
                ->subject(__('messages.labels.verify_email_subject'));
        });

        if (! App::runningInConsole()) {
            SubscriptionPlan::observe(SubscriptionPlanObserver::class);
            Template::observe(TemplateObserver::class);
        }
        Message::observe(MessageObserver::class);
        Patient::observe(PatientObserver::class);

        Gate::policy(Template::class, TemplatePolicy::class);
        Gate::policy(PaymentMethod::class, PaymentMethodPolicy::class);

        view()->composer('clinic.*', function ($view) {
            if (! App::runningInConsole()) {
                $user = Auth::user();

                if ($user && $user->hasRole('doctor')) {
                    $view->with('isClinicSuspended', $user?->clinics?->is_suspended == 1);
                    $view->with('unReadNotifications', $user?->clinics?->unReadNotifications ?? collect([]));
                }
            }
        });
    }
}
