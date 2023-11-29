<?php

namespace App\Services\Otp;

use App\Constants\CacheKeys;
use App\Exceptions\Auth\TokenLimitErrorException;
use App\Notifications\OTPTokenNotification;
use Cache;

class OtpService
{
    public function checkOTPSentCountLimit($user)
    {
        $cacheKey = CacheKeys::OTPSentCountKey($user->email);
        $otpTimeout = now()->addMinutes(config('auth.otp_send_timeout_minute'));

        $otpSentCount = Cache::remember($cacheKey, $otpTimeout, fn () => 0);

        if ($otpSentCount == config('auth.otp_send_limit_count')) {
            throw new TokenLimitErrorException();
        }

        return Cache::increment($cacheKey);
    }

    public function checkOTPTryCountLimit($user)
    {
        $cacheKey = CacheKeys::OTPTryCountKey($user->email);
        $otpTimeout = now()->addMinutes(config('auth.otp_try_timeout_minute'));

        $otpTryCount = Cache::remember($cacheKey, $otpTimeout, fn () => 0);

        if ($otpTryCount == config('auth.otp_try_limit_count')) {
            throw new TokenLimitErrorException();
        }

        return Cache::increment($cacheKey);
    }

    public function resetOTPSentCountLimit($user)
    {
        $cacheKey = CacheKeys::OTPSentCountKey($user->email);
        Cache::forget($cacheKey);
        return true;
    }

    public function resetOTPTryCountLimit($user)
    {
        $cacheKey = CacheKeys::OTPTryCountKey($user->email);
        Cache::forget($cacheKey);
        return true;
    }

    public function generateOTP($user)
    {
        $otp = random_int(100000, 999999);
        Cache::set(CacheKeys::OTPKey($user->email, $otp), $user->id, now()->addMinutes(config('auth.otp_send_timeout_minute')));
        return $otp;
    }

    public function sendOTPToken($user, $title)
    {
        return $user->notify(new OTPTokenNotification($this->generateOTP($user), $title));
    }
}
