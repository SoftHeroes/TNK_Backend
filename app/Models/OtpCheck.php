<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class OtpCheck extends Model
{
    protected $table = 'otpCheck';
    protected $primaryKey = 'PID';

    const CREATED_AT = 'createdAt';

    public function checkOtpValid ($otp) {

        $currentDateTime = microtimeToDateTime(getCurrentTimeStamp());

        return $this->where('otp', '=', $otp)
                    ->where('validTill', '>', $currentDateTime);
    }
}
