<?php

namespace App\Helpers;

use App\Repositories\TwoFactorOTPRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class OTPHelper
{
    protected TwoFactorOTPRepository $twoFactorOTPRepository;

    /**
     * Method __construct.
     *
     * @param TwoFactorOTPRepository $twoFactorOTPRepository
     *
     * @return void
     */
    public function __construct(TwoFactorOTPRepository $twoFactorOTPRepository)
    {
        $this->twoFactorOTPRepository = $twoFactorOTPRepository;
    }

    /**
     * Generate a random 6-digit OTP and store it in the database.
     *
     * @param string $userId User ID for whom the OTP is generated
     *
     * @return string|null Generated OTP or null if failed to generate
     */
    public function generateAndStoreOTP($userId)
    {
        $otp = self::generateOTP();
        $expirationTimestamp = Carbon::now()->addMinutes(config('constants.GLOBAL.OTP.OTP_EXPIRATION_MINUTES'));
        if ($otp !== null) {
            $data = [
                'user_id' => $userId,
                'otp_code' => Crypt::encrypt($otp),
                'resend_count' => 0,
                'last_sent_at' => now(),
                // 'last_attempt_time' => null,
                'verified_at' => null,
                'expired_at' => $expirationTimestamp,
            ];
            $this->twoFactorOTPRepository->store($data);
        }

        return $otp;
    }

    /**
     * Generate a random 6-digit OTP (One-Time Password).
     *
     * @return string
     */
    private static function generateOTP()
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
