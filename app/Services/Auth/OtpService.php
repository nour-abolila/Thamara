<?php

namespace App\Services\Auth;

use App\Mail\OtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    const OTP_EXPIRES_MINUTES = 10;
    const MAX_ATTEMPTS = 5;
    const RESEND_MINUTES = 2;


    public function generateOtp(User $user)
    {
        // منع توليد كود جديد قبل ما الـ cooldown بتاع الـ resend يخلص
        if (
            $user->otp_last_sent_at
            && $user->otp_last_sent_at->addMinutes(self::RESEND_MINUTES)->isAfter(now())
        ) {
            return null;
        }

        $otp = rand(100000, 999999);

        $user->otp_code = Hash::make($otp);
        $user->otp_expires_at = Carbon::now()->addMinutes(self::OTP_EXPIRES_MINUTES);
        $user->otp_attempts = 0;
        $user->otp_last_sent_at = Carbon::now();
        $user->save();

        return $otp;
    }



    public function sendOtpEmail(User $user, $otp)
    {
        Mail::to($user->email)->send(new OtpMail($otp));
    }



    public function verifyOtp(User $user, $otp): bool
    {
        // التحقق من وجود كود OTP وصلاحيته
        if (!$user->otp_code || !$user->otp_expires_at) {
            return false;
        }

        // لو المستخدم تجاوز عدد المحاولات المسموح بيها
        if ($user->otp_attempts >= self::MAX_ATTEMPTS) {
            $this->clearOtp($user);
            return false;
        }


        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            $this->clearOtp($user);
            return false;
        }


        if (!Hash::check($otp, $user->otp_code)) {
            // تسجيل محاولة فاشلة
            $user->increment('otp_attempts');
            return false;
        }


        $this->clearOtp($user);
        return true;
    }



    public function clearOtp(User $user)
    {
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->otp_attempts = 0;
        $user->save();
    }
}
