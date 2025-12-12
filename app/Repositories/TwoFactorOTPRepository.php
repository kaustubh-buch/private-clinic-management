<?php

namespace App\Repositories;

use App\Models\TwoFactorOtp;

class TwoFactorOTPRepository extends CommonRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct(TwoFactorOtp $model)
    {
        parent::__construct($model);
    }

    /**
     * Retrieve an unexpired OTP (One-Time Password) for a specific user.
     *
     * @param string $user_id The ID of the user for whom the OTP is to be retrieved.
     *
     * @return TwoFactorOtp|null The first unexpired OTP record for the user, or null if not found.
     */
    public function getOtpForUser(string $user_id)
    {
        return $this->model->where('user_id', $user_id)->where('expired_at', '>', now())->latest()->first();
    }

    /**
     * Expire all other non-expired OTPs for the user.
     *
     * @param string $user_id The ID of the user
     *
     * @return void
     */
    public function expireNonExpiredOTPs(string $user_id)
    {
        $nonExpiredOTPs = $this->model->where('user_id', $user_id)
            ->where('expired_at', '>', now())
            ->latest()
            ->get();

        foreach ($nonExpiredOTPs as $otp) {
            $otp->expired_at = now();
            $otp->save();
        }
    }
}
