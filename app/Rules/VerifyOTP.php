<?php

namespace App\Rules;

use App\Services\OTPService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class VerifyOTP implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $otpService = app(OTPService::class);
        $response = $otpService->verifyOtp(Auth::user(), $value);

        if ($response === $otpService::OTP_VERIFICATION_SUCCESSFUL) {
            return; // validation passes
        }

        if ($response === $otpService::OTP_ATTEMPTS_EXCEEDED_BLOCKED) {
            $fail(__('messages.alerts.too_many_attempts'));

            return;
        }

        $fail(__('messages.validation.invalid_otp'));
    }
}
