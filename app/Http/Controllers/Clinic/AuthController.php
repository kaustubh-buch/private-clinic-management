<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\LoginRequest;
use App\Http\Requests\Clinic\ResetPasswordRequest;
use App\Http\Requests\Clinic\SendPasswordResetLinkRequest;
use App\Http\Requests\Clinic\SignupEmailRequest;
use App\Http\Requests\Clinic\SignupRequest;
use App\Models\User;
use App\Repositories\AuthTokenRepository;
use App\Repositories\UserRepository;
use App\Services\OTPService;
use App\Services\VerifyEmailService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    private UserRepository $userRepository;

    private AuthTokenRepository $authTokenRepository;

    private VerifyEmailService $emailVerifyService;

    private OTPService $otpService;

    /**
     * AuthController constructor.
     *
     * @param UserRepository      $userRepository
     * @param AuthTokenRepository $authTokenRepository
     * @param VerifyEmailService  $emailVerifyService
     * @param OTPService          $otpService
     */
    public function __construct(
        UserRepository $userRepository,
        AuthTokenRepository $authTokenRepository,
        VerifyEmailService $emailVerifyService,
        OTPService $otpService,
    ) {
        $this->userRepository = $userRepository;
        $this->authTokenRepository = $authTokenRepository;
        $this->emailVerifyService = $emailVerifyService;
        $this->otpService = $otpService;
    }

    /**
     * Show the login form.
     *
     * @return View
     */
    public function showLoginForm(): View
    {
        return view('clinic.pages.auth.login');
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

        if (! $user || ! $user->hasRole(config('constants.GLOBAL.ROLES.DOCTOR'))) {
            return back()->withInput()->with('login_error', __('messages.alerts.invalid_credentials'));
        }

        $credentials = $request->only('email', 'password');

        if (Auth::validate($credentials)) {

            if ($user->is_blocked) {
                return back()->with('login_error', __('messages.alerts.too_many_attempts'));
            }

            if (is_null($user->email_verified_at)) {
                session()->put('verify_user_id', $user->id);

                return redirect()->route('email.verify');
            }

            if ($this->shouldTrigger2FA($user, $request)) {
                session()->put('2fa:user_id', $user->id);
                session()->put('2fa:context', 'login');

                $mobile_country_code = config('constants.GLOBAL.DEFAULT_COUNTRY_CODE');
                $mobile_no = $mobile_country_code.''.$user->clinics->mobile_no;
                $this->otpService->sendOTP($user->id, $mobile_no, 1);
                $this->userRepository->resetLoginAttemptCounts($user->id);

                return redirect()->route('2fa.verify')->with('success', __('messages.alerts.verification_code_sent_success'));
            }

            Auth::login($user);
            $request->session()->regenerate();
            $this->userRepository->resetLoginAttemptCounts($user->id);
            $this->userRepository->update($user->id, ['last_logged_in_at' => now()]);

            return redirect()->route('dashboard');
        }

        $this->userRepository->handleFailedLoginAttempt($user);

        return back()->with('login_error', __('messages.alerts.invalid_credentials'))->withInput();
    }

    /**
     * Determines whether Two-Factor Authentication (2FA) should be triggered for the user.
     *
     * @param User         $user    The user attempting to log in.
     * @param LoginRequest $request The login request containing user credentials and cookies.
     *
     * @return bool True if 2FA should be triggered, false otherwise.
     */
    private function shouldTrigger2FA($user, LoginRequest $request): bool
    {
        if (! $user->requires2FA()) {
            return false;
        }

        $token = $request->cookie('2fa_token');

        if (! $token) {
            return true;
        }

        return ! $this->authTokenRepository->tokenExistsForUser(
            $user,
            $token
        );
    }

    /**
     * Show the forgot password form.
     *
     * @return View
     */
    public function showForgotPasswordForm()
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

        return view('clinic.pages.auth.forgot-password', compact('remainingCooldown'));
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

        if ($user && $user->hasRole(config('constants.GLOBAL.ROLES.DOCTOR'))) {
            $lastSentAt = session('last_password_reset_sent_at_email_'.md5($email));
            if ($lastSentAt) {
                $lastSentAt = Carbon::parse($lastSentAt);
                $diff = $lastSentAt->diffInSeconds(now());
                $cooldownTime = config('constants.GLOBAL.RESEND_EMAIL_COOLDOWN');

                if ($diff > 0 && $diff < $cooldownTime) {
                    $remainingCooldown = (int) ($cooldownTime - $diff);

                    return back()->withInput()->with('error', 'You can request another email after some time.');
                }
            }

            Password::sendResetLink(
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

        if (! $user || ! $user->hasRole(config('constants.GLOBAL.ROLES.DOCTOR'))) {
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
            // If there were validation errors, show the form again (don't redirect to 2FA)
            $viewData['expired'] = false;
        } elseif (session('2fa:verified_email') === $email && session('2fa:context') === 'password_reset') {
            // If already 2FA verified
            session()->forget(['2fa:verified_email', '2fa:context']);
        } elseif ($user->requires2FA()) {
            // If user has mobile, redirect to 2FA flow
            session()->put('2fa:user_id', $user->id);
            session()->put('2fa:context', 'password_reset');
            session()->put('2fa:reset_token', $token);
            session()->put('2fa:reset_email', $email);

            return redirect()->route('enter-code');
        }

        return view('clinic.pages.auth.reset-password', $viewData);
    }

    /**
     * Show the form to enter a code.
     *
     * @return RedirectResponse|View
     */
    public function showSendCodeForm(Request $request): RedirectResponse|View
    {
        $user = $this->userRepository->find(session('2fa:user_id', 0));

        if (! $user) {
            return redirect()->route('login')->with('error', __('messages.alerts.user_not_found'));
        }

        return view('clinic.pages.auth.enter-code', compact('user'));
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

        return view('clinic.pages.auth.two-factor-authentication', compact('user'));
    }

    /**
     * Show the first step of the signup form.
     *
     * @return View
     */
    public function signupStepOneForm(): View
    {
        return view('clinic.pages.auth.signup-step-one');
    }

    /**
     * Show the second step of the signup form.
     *
     * @return RedirectResponse|View
     */
    public function signupStepTwoForm(): RedirectResponse|View
    {
        if (! session()->has('signup.email')) {
            return redirect()->route('signup.step.one')->withErrors(['email' => __('messages.validation.email_required_first')]);
        }

        return view('clinic.pages.auth.signup-step-two');
    }

    /**
     * Store the signup email in the session and proceed to the next step.
     *
     * @param SignupEmailRequest $request
     *
     * @return RedirectResponse
     */
    public function storeSignUpEmail(SignupEmailRequest $request): RedirectResponse
    {
        session(['signup.email' => $request->input('email'), 'signup.receives_promotional_emails' => $request->has('receives_promotional_emails')]);

        return redirect()->route('signup.step.two');
    }

    /**
     * Handle the signup process and create a new user.
     *
     * @param SignupRequest $request
     *
     * @return RedirectResponse
     */
    public function signup(SignupRequest $request): RedirectResponse
    {
        // Ensure email exists in session
        if (! session()->has('signup.email')) {
            return redirect()->route('signup.step.one')
                ->withErrors(['email' => __('messages.validation.email_required_first')]);
        }

        // Split full name into first and last
        $nameParts = explode(' ', $request->input('full_name'), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        // Merge all data
        $userData = [
            'email' => session('signup.email'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password' => bcrypt($request->input('password')),
            'company_name' => $request->input('company_name'),
            'company_abn' => $request->input('company_abn'),
            'dob' => Carbon::createFromFormat(
                config('constants.GLOBAL.DATE_FORMAT_D_M_Y'),
                $request->input('dob')
            )->format(config('constants.GLOBAL.DATE_FORMAT_Y_M_D')),
            'receives_promotional_emails' => session('signup.receives_promotional_emails') ? 1 : 0,
        ];

        $user = $this->userRepository->store($userData);
        $user->assignRole(config('constants.GLOBAL.ROLES.DOCTOR'));

        session()->forget('signup');
        session()->put('verify_user_id', $user->id);

        $token = Str::random(60);

        $data = [
            'user_id' => $user->id,
            'token' => $token,
            'token_type' => 'email_verification',
        ];

        $this->authTokenRepository->store($data);

        $this->emailVerifyService->sendVerificationMail($user);

        return redirect()->route('email.verify')->with('success', __('messages.alerts.email_successful_sent'));
    }

    /**
     * Show the email verification form.
     *
     * @return View
     */
    public function showVerifyEmail(): View
    {
        return view('clinic.pages.auth.verify-email');
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
            return redirect()->back()->with('forgot_password_success', true); // Pass success flag
        } else {
            return redirect()->back()
                ->with('error', __('messages.global.something_went_wrong'));
        }
    }
}
