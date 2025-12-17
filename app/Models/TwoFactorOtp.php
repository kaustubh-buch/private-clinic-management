<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TwoFactorOtp extends Model
{
    use HasUuids;
    protected $guarded = [];

    /**
     * The getOtp function returns the decrypted value of the otp_code property.
     *
     * @return mixed
     */
    public function getOtp()
    {
        return Crypt::decrypt($this->otp_code);
    }
}
