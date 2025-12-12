<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOTPRequest;
use App\Repositories\UserRepository;
use App\Services\OTPService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    protected $otpService;

    protected $userRepository;

    /**
     * Create a new controller instance.
     *
     * @param OTPService     $otpService
     * @param UserRepository $userRepository
     */
    public function __construct(
        OTPService $otpService,
        UserRepository $userRepository
    ) {
        $this->otpService = $otpService;
        $this->userRepository = $userRepository;
    }

    /**
     * Verifies the OTP submitted by the user.
     * Handles 2FA for login.
     *
     * @param VerifyOTPRequest $request
     *
     * @return JsonResponse|RedirectResponse
     */
    public function verify(VerifyOTPRequest $request): JsonResponse|RedirectResponse
    {
        $isAjax = $request->ajax();

        $user = Auth::user() ?? $this->userRepository->find(session('2fa:user_id'));

        if (! $user) {
            return $this->handleResponse($isAjax, false, ['redirect' => route('login')]);
        }

        $response = $this->otpService->verifyOTP($user, $request->otp);

        if ($response == OTPService::OTP_VERIFICATION_FAILED) {
            return $this->handleResponse($isAjax, false, ['otp_error' => __('messages.validation.invalid_otp')]);
        } elseif ($response == OTPService::OTP_ATTEMPTS_EXCEEDED_BLOCKED) {
            Auth::logout();

            return $this->handleResponse($isAjax, false, ['is_blocked' => true]);
        }

        // ✅ Clear 2FA session but preserve context info
        session()->forget('2fa:user_id');

        $context = session('2fa:context');

        switch ($context) {
            case 'login':
                session()->forget('2fa:context');
                Auth::loginUsingId($user->id);
                $this->userRepository->resetLoginAttemptCounts($user->id);
                $this->userRepository->update($user->id, ['last_logged_in_at' => now()]);

                return redirect()->route('welcome');

            default:
                return redirect()->route('login');
        }
    }

    /**
     * Resends a new OTP to the user if allowed.
     *
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse
     */
    public function resendOtp(Request $request)
    {
        $isAjax = $request->ajax();
        $user = Auth::user() ?? $this->userRepository->find(session('2fa:user_id'));

        if (! $user) {
            return $this->handleResponse($isAjax, false, [
                'message' => __('messages.alerts.user_not_found'),
                'redirect' => route('login'),
            ]);
        }
        
        $secondsLeft = $this->otpService->checkForResend($user->id);
        if ($secondsLeft > 0) {
            return $this->handleResponse($isAjax, false, ['secondsLeft' => $secondsLeft]);
        }

        $email = ! empty($request->email) ? $request->email : $user->email;

        $this->otpService->sendOTP($user->id, $email, 0);

        return $this->handleResponse($isAjax, true, [
            'message' => __('messages.alerts.otp_resent_success'),
        ]);
    }

    /**
     * Sends an OTP to the user's email based on the current context (e.g. password reset).
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sendCode(Request $request)
    {
        $isAjax = $request->ajax();
        $userId = Auth::id() ?? session('2fa:user_id');
        $user = $this->userRepository->find($userId);

        if (! $user) {
            return $this->handleResponse($isAjax, false, [
                'message' => __('messages.alerts.user_not_found'),
                'redirect' => route('login'),
            ]);
        }

        if ($request->context) {
            session()->put('2fa:context', $request->context);
        }

        if (session('2fa:context') == 'password_reset') {
            session()->put('2fa:code_sent', true);
        }

        $email = ! empty($request->email) ? $request->email : $user->email;

        $this->otpService->sendOTP($user->id, $email, 1);

        return response()->json(['success' => true]);
    }

    /**
     * Central handler for both Ajax and normal responses.
     *
     * @param bool  $isAjax  Indicates if the request is an Ajax request.
     * @param bool  $success Indicates if the operation was successful.
     * @param array $data    Additional data to include in the response.
     * @param bool  $isAdmin Indicates if the user is an admin (default: false).
     *
     * @return JsonResponse|RedirectResponse
     */
    private function handleResponse(bool $isAjax, bool $success, array $data = [], bool $isAdmin = false)
    {
        if ($isAjax) {
            return response()->json(array_merge(['success' => $success], $data));
        }

        if (isset($data['redirect'])) {
            return redirect($data['redirect'])->with($success ? 'success' : 'error', $data['message'] ?? null);
        }

        if (isset($data['otp_error'])) {
            return back()->with('otp_error', $data['otp_error']);
        }

        if (isset($data['message'])) {
            return back()->with($success ? 'success' : 'error', $data['message']);
        }

        if (isset($data['secondsLeft'])) {
            return redirect()->route('2fa.verify')->with('secondsLeft', $data['secondsLeft']);
        }

        if ($data['is_blocked']) {
            return redirect()->route('login')->with('login_error', __('messages.alerts.too_many_attempts'));
        }

        return back();
    }
}
