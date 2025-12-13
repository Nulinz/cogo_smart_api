<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class Otp
{
    protected $authKey = '373776616e616e38313500';

    protected $senderId = 'ONSTRU';

    protected $route = '2';

    protected $country = '91';

    protected $dltTeId = '1707172965885916248';

    /**
     * Send OTP to user via SMS API
     */
    public function sendOtp(string $phone, string $otp): bool
    {
        // $message = urlencode("Dear user, your DriversDeck registration OTP is $otp. Please do not share this with anyone. - DRDECK");
        $message = urlencode("welcome Your verification code is: $otp. Please enter this code to complete your registration. - ONSTRU");

        $url = 'http://promo.smso2.com/api/sendhttp.php?'
            ."authkey={$this->authKey}"
            ."&mobiles={$phone}"
            ."&message={$message}"
            ."&sender={$this->senderId}"
            ."&route={$this->route}"
            ."&country={$this->country}"
            ."&DLT_TE_ID={$this->dltTeId}";

        try {
            $response = file_get_contents($url);
            Log::info("SMS sent to $phone. OTP: $otp. Response: $response");

            return true;
        } catch (\Exception $e) {
            Log::error('SMS sending failed: '.$e->getMessage());

            return false;
        }
    }
}
