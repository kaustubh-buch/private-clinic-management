<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Admin\SendPasswordResetLinkRequest;
use App\Models\User;
use App\Repositories\AuthTokenRepository;
use App\Repositories\UserRepository;
use App\Services\OTPService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    private $userRepository;

    private $otpService;

    private $authTokenRepository;

    /**
     * AdminAuthController constructor.
     *
     * @param UserRepository      $userRepository      The repository for managing user data.
     * @param OTPService          $otpService          The service for handling OTP (One-Time Password) operations.
     * @param AuthTokenRepository $authTokenRepository
     */
    public function __construct(
        UserRepository $userRepository,
        OTPService $otpService,
        AuthTokenRepository $authTokenRepository
    ) {
        $this->userRepository = $userRepository;
        $this->otpService = $otpService;
        $this->authTokenRepository = $authTokenRepository;
    }

    /**
     * Show the login form.
     *
     * @return View
     */
    public function showLoginForm()
    {
        return view('admin.pages.auth.login');
    }

    /**
     * Show the forgot password form.
     *
     * @return View
     */
    public function showForgotPasswordForm()
    {
        return view('admin.pages.auth.forgot-password');
    }

    /**
     * Show the reset password form.
     *
     * @return View
     */
    public function showResetForm(Request $request, string $token)
    {
        $expired = true;
        if ($request->email) {
            $user = $this->userRepository->getUserByMailId($request->email);
            if ($user) {
                $expired = ! (Password::tokenExists($user, $token));
            }
        }

        return view('admin.pages.auth.reset-password', ['token' => $token, 'email' => $request->email, 'expired' => $expired]);
    }

    /**
     * Handle admin login.
     *
     * @param AdminLoginRequest $request
     *
     * @return RedirectResponse
     */
    public function login(AdminLoginRequest $request)
    {
        $email = $request->input('email');
        $user = $this->userRepository->getUserByMailId($email);

        if (! $user || ! $user->hasRole('admin')) {
            return back()->with('error', __('messages.alerts.invalid_credentials'));
        }

        if ($user->is_blocked) {
            return back()->with('error', __('messages.alerts.too_many_attempts'));
        }

        $credentials = $request->only('email', 'password');
        $isRemember = $request->boolean('remember');

        if (Auth::validate($credentials)) {
            $this->userRepository->resetLoginAttemptCounts($user->id);

            if ($user->requires2FA()) {
                session()->put('2fa:user_id', $user->id);
                session()->put('2fa:context', 'login');
                $mobile_no = $user->country_code.''.$user->phone_number;
                $this->otpService->sendOTP($user->id, $mobile_no, 1);

                return redirect()->route('admin.2fa.verify')->with('status', __('messages.alerts.otp_sent_success'));
            }
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        if (Auth::attempt($credentials, $isRemember)) {
            $this->userRepository->resetLoginAttemptCounts($user->id);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        $this->userRepository->handleFailedLoginAttempt($user);

        return back()->with('error', __('messages.alerts.invalid_credentials'));
    }

    /**
     * Handle admin logout and session cleanup.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', __('messages.alerts.logout_success'));
    }

    /**
     * Send a password reset link to the given email.
     *
     * @param SendPasswordResetLinkRequest $request
     *
     * @return RedirectResponse
     */
    public function sendPasswordResetLink(SendPasswordResetLinkRequest $request)
    {
        $email = $request->input('email');

        $user = $this->userRepository->getUserByMailId($email);

        if (! $user || ! $user->hasRole('admin')) {
            return back()->with('error', __('messages.alerts.invalid_email'));
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::ResetLinkSent
            ? back()->with(['success' => __('messages.alerts.email_sent_success')])
            : back()->with(['error' => __('messages.global.something_went_wrong')]);
    }

    /**
     * Handle password reset for the user.
     *
     * @param ResetPasswordRequest $request
     *
     * @return RedirectResponse
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = $password;
                $user->save();
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')
                ->with('success', __('messages.alerts.password_reset_success'));
        } else {
            return redirect()->back()
                ->with('error', __('messages.global.something_went_wrong'));
        }
    }

    /**
     * Show the two-factor authentication form.
     *
     * @return View|RedirectResponse
     */
    public function show2FAForm(): View | RedirectResponse
    {
        if (! session()->has('2fa:user_id')) {
            return redirect()->route('login');
        }

        $user = $this->userRepository->find(session('2fa:user_id', 0));

        if (! $user || ! $user->hasRole(config('constants.GLOBAL.ROLES.ADMIN'))) {
            return redirect()->route('login')->with('error', __('messages.alerts.user_not_found'));
        }

        return view('admin.pages.auth.2fa');
    }
}
