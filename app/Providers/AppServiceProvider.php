<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset',
                ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()],
                false
            ));

            return (new MailMessage)
                ->subject(__('messages.labels.reset_password_email_title'))
                ->view('email-template.reset-password-email', [
                    'resetUrl' => $url,
                    'user' => $notifiable,
                ]);
        });

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $params = ['token' => $token, 'email' => $user->email];

            return route('password.reset', $params);
        });
    }
}
