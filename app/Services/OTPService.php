<?php

namespace App\Services;

use App\Helpers\OTPHelper;
use App\Mail\TwoFactorVerificationMail;
use App\Models\User;
use App\Repositories\TwoFactorOTPRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie as HttpFoundationCookie;

class OTPService
{
    protected OTPHelper $otpHelper;

    private TwoFactorOTPRepository $twoFactorOTPRepository;

    private UserRepository $userRepository;

    public $maxAttempt;

    public $lockMinutes;

    /**
     * Constant representing password is invalid.
     *
     * @var string
     */
    public const OTP_VERIFICATION_FAILED = 'OTP_VERIFICATION_FAILED';

    /**
     * Constant representing otp attempts has been exceeded.
     *
     * @var string
     */
    public const OTP_ATTEMPTS_EXCEEDED_BLOCKED = 'OTP_ATTEMPTS_EXCEEDED_BLOCKED';

    /**
     * Constant representing otp verification success.
     *
     * @var string
     */
    public const OTP_VERIFICATION_SUCCESSFUL = 'OTP_VERIFICATION_SUCCESSFUL';

    /**
     * OTPService constructor.
     *
     * @param OTPHelper              $otpHelper              Helper class to manage OTP generation and validation.
     * @param TwoFactorOTPRepository $twoFactorOTPRepository Repository to interact with the two_factor_otps table.
     * @param UserRepository         $userRepository         Repository to interact with the users table.
     */
    public function __construct(
        OTPHelper $otpHelper,
        TwoFactorOTPRepository $twoFactorOTPRepository,
        UserRepository $userRepository
    ) {
        $this->otpHelper = $otpHelper;
        $this->twoFactorOTPRepository = $twoFactorOTPRepository;
        $this->userRepository = $userRepository;
        $this->maxAttempt = config('auth.login_attempt.max_attempts');
        $this->lockMinutes = config('auth.login_attempt.lock_minutes');
    }

    /**
     * Send a One-Time Password (OTP) to the user's email (for testing).
     *
     * @param string      $user_id       The ID of the user to whom the OTP is being sent.
     * @param string|null $email         Email to which the OTP is sent.
     * @param bool        $flag          If true, force generation of a new OTP and expire previous ones.
     *
     * @return void
     */
    public function sendOTP(string $user_id, ?string $email, bool $flag = false): void
    {
        $otp = null;

        if ($flag === true) {
            // Used when changing number or when status is already on
            $this->twoFactorOTPRepository->expireNonExpiredOTPs($user_id);
            $otp = $this->otpHelper->generateAndStoreOTP($user_id);
        } else {
            // Used when enabling 2FA or resending OTP
            $expectedOTP = $this->twoFactorOTPRepository->getOtpForUser($user_id);
            if (is_null($expectedOTP)) {
                $otp = $this->otpHelper->generateAndStoreOTP($user_id);
            } else {
                $this->updateLastSentAt($expectedOTP->id);
                $otp = $expectedOTP->getOtp();
            }
        }

        $user = $this->userRepository->getModel()->where('id', $user_id)->first();
        Mail::to($email)->send(new TwoFactorVerificationMail($otp,$user));
        logger($otp);
    }

    /**
     * Update the last_sent_at timestamp for a given OTP entry.
     *
     * @param string $otp_id The ID of the OTP record to update.
     *
     * @return void
     */
    public function updateLastSentAt(string $otp_id): void
    {
        $this->twoFactorOTPRepository->update($otp_id, ['last_sent_at' => now()]);
    }

    /**
     * Check if an OTP can be resent based on the verification cooldown timer.
     *
     *
     * @param string $user_id The ID of the user for whom the resend eligibility is being checked.
     *
     * @return int The number of seconds remaining before resend is allowed. Returns 0 if resend is allowed.
     */
    public function checkForResend(string $user_id): int
    {
        $row = $this->twoFactorOTPRepository->getOtpForUser($user_id);
        if (is_null($row)) {
            return 0;
        }

        $last_sent_at = $row->last_sent_at;
        $verification_timer = config('constants.GLOBAL.VERIFICATION_TIMER');

        if ($last_sent_at && Carbon::parse($last_sent_at)->diffInSeconds(now()) < $verification_timer) {
            return $verification_timer - Carbon::parse($last_sent_at)->diffInSeconds(now());
        }

        return 0;
    }

    /**
     * Verify the user's 2FA OTP.
     *
     * This method checks whether the provided OTP matches the one stored for the user.
     * If matched, it resets the OTP attempt count and updates the verification timestamp.
     *
     * @param User   $user The user for whom the OTP is being verified.
     * @param string $otp  The One-Time Password entered by the user.
     *
     * @return string One of the predefined constants indicating success or failure.
     */
    public function verifyOtp(User $user, string $otp): string
    {
        $row = $this->twoFactorOTPRepository->getOtpForUser($user->id);

        if (! is_null($row)) {
            if ($otp === $row->getOtp()) {
                $this->updateOtpCount($row->id, 0, now());

                return self::OTP_VERIFICATION_SUCCESSFUL;
            } elseif ($row->resend_count + 1 > $this->maxAttempt) {
                $this->updateOtpCount($row->id, $row->resend_count + 1);
                $blockUntil = now()->addMinutes($this->lockMinutes);
                $updated = $this->userRepository->update($user->id, ['blocked_until' => $blockUntil]);

                return self::OTP_ATTEMPTS_EXCEEDED_BLOCKED;
            } else {
                $this->updateOtpCount($row->id, $row->resend_count + 1);

                return self::OTP_VERIFICATION_FAILED;
            }
        }

        return self::OTP_VERIFICATION_FAILED;
    }

    /**
     * Update the OTP attempt record with resend count, verification time, and optional expiration time.
     *
     *
     * @param string      $otp_attempts_id The ID of the OTP attempt record to update.
     * @param int         $attempt_count   The number of resend attempts to be stored.
     * @param string|null $expired_at      The optional timestamp when the OTP expired (Y-m-d H:i:s format).
     * @param string|null $verified_at     The optional timestamp when the OTP was verified (Y-m-d H:i:s format). Defaults to now().
     *
     * @return void
     */
    public function updateOtpCount(
        string $otp_attempts_id,
        int $attempt_count,
        ?string $expired_at = null,
        ?string $verified_at = null
    ): void {
        $data = [
            'resend_count' => $attempt_count,
            'verified_at' => $verified_at ?? now(),
            // 'last_attempt_time' => now(), // optionally track last attempt time
        ];

        if ($expired_at !== null) {
            $data['expired_at'] = $expired_at;
        }

        $this->twoFactorOTPRepository->update($otp_attempts_id, $data);
    }
}
