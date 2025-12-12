<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendPasswordResetLinkRequest;
use App\Repositories\UserRepository;
use App\Services\OTPService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    private UserRepository $userRepository;

    private OTPService $otpService;

    /**
     * AuthController constructor.
     *
     * @param UserRepository      $userRepository
     * @param OTPService          $otpService
     */
    public function __construct(
        UserRepository $userRepository,
        OTPService $otpService
    ) {
        $this->userRepository = $userRepository;
        $this->otpService = $otpService;
    }

    /**
     * Show the login form.
     *
     * @return View
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle the login process for the user.
     *
     * @param LoginRequest $request
     *
     * @return RedirectResponse
     */
    public function login(LoginRequest $request)
    {
        $email = $request->input('email');
        $user = $this->userRepository->getUserByMailId($email);

        if (! $user) {
            return back()->withInput()->with('login_error', __('messages.alerts.invalid_credentials'));
        }

        $credentials = $request->only('email', 'password');

        if (Auth::validate($credentials)) {
            if ($user->is_blocked) {
                return back()->with('login_error', __('messages.alerts.too_many_attempts'));
            }
            session()->put('2fa:user_id', $user->id);
            session()->put('2fa:context', 'login');
            
            $this->otpService->sendOTP($user->id, $user->email, 1);
            $this->userRepository->resetLoginAttemptCounts($user->id);

            return redirect()->route('2fa.verify');
        }

        $this->userRepository->handleFailedLoginAttempt($user);

        return back()->with('login_error', __('messages.alerts.invalid_credentials'));
    }

    /**
     * Log out the authenticated user and invalidate the session.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show the two-factor authentication form.
     *
     * @return RedirectResponse|View
     */
    public function showTwoFactorForm(): RedirectResponse|View
    {
        if (! session()->has('2fa:user_id')) {
            return redirect()->route('login');
        }

        $user = $this->userRepository->find(session('2fa:user_id', 0));

        if (! $user) {
            return redirect()->route('login')->with('error', __('messages.alerts.user_not_found'));
        }

        return view('auth.two-factor-authentication', compact('user'));
    }

    /**
     * Show the forgot password form.
     *
     * @return View
     */
    public function showForgotPasswordForm(): View
    {
        $remainingCooldown = 0;
        $email = old('email');

        if ($email) {
            $sessionKey = 'last_password_reset_sent_at_email_'.md5($email);
            $lastSent = session($sessionKey);

            if ($lastSent) {
                $diff = $lastSent->diffInSeconds(now());
                $cooldownTime = config('constants.GLOBAL.RESEND_EMAIL_COOLDOWN');

                if ($diff > 0 && $diff < $cooldownTime) {
                    $remainingCooldown = (int) ($cooldownTime - $diff);
                }
            }
        }

        return view('auth.forgot-password', compact('remainingCooldown'));
    }

    /**
     * Send a password reset link to the user's email.
     *
     * @param SendPasswordResetLinkRequest $request
     *
     * @return RedirectResponse
     */
    public function sendPasswordResetLink(SendPasswordResetLinkRequest $request): RedirectResponse
    {
        $email = $request->input('email');
        $user = $this->userRepository->getUserByMailId($email);
        if ($user) {
            $lastSentAt = session('last_password_reset_sent_at_email_'.md5($email));
            if ($lastSentAt) {
                $lastSentAt = Carbon::parse($lastSentAt);
                $diff = $lastSentAt->diffInSeconds(now());
                $cooldownTime = config('constants.GLOBAL.RESEND_EMAIL_COOLDOWN');

                if ($diff > 0 && $diff < $cooldownTime) {
                    $remainingCooldown = (int) ($cooldownTime - $diff);

                    return back()->withInput()->with('error', __('messages.alerts.password_reset_attempt'));
                }
            }

            $status = Password::sendResetLink(
                $request->only('email')
            );
            session(['last_password_reset_sent_at_email_'.md5($email) => now()]);
        } else {
            return back()->withInput()->with('error', __('messages.alerts.invalid_email'));
        }

        return back()->withInput()->with(['sent' => true]);
    }

    /**
     * Show the reset password form.
     *
     * @return RedirectResponse|View
     */
    public function showResetPasswordForm(Request $request, string $token): RedirectResponse|View
    {
        $email = $request->email;

        if (! $email) {
            return redirect()->route('login')->with('error', __('messages.alerts.reset_link_expired'));
        }

        $user = $this->userRepository->getUserByMailId($email);

        if (! $user) {
            return redirect()->route('login')->with('error', __('messages.alerts.invalid_email'));
        }

        $expired = ! Password::tokenExists($user, $token);
        if (! session('forgot_password_success') && $expired) {
            return redirect()->route('login')->with('error', __('messages.alerts.reset_link_expired'));
        }

        // Prepare default view data
        $viewData = [
            'token' => $token,
            'email' => $email,
            'expired' => $expired,
            'reset_done' => false,
        ];

        if (session('forgot_password_success')) {
            // If form was submitted successfully
            $viewData['expired'] = false;
            $viewData['reset_done'] = true;
        } elseif ($request->old() || $request->session()->has('errors')) {
            // If there were validation errors, show the form again
            $viewData['expired'] = false;
        }

        return view('auth.reset-password', $viewData);
    }
    /**
     * Handle password reset for the user.
     *
     * @param ResetPasswordRequest $request
     *
     * @return RedirectResponse
     */
    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = $password;
                $user->save();
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('forgot_password_success', true); // Pass success flag
        } else {
            return redirect()->back()
                ->with('error', __('messages.global.something_went_wrong'));
        }
    }
}
