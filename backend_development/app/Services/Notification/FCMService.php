<?php
// app/Services/FCMService.php

namespace App\Services;

use App\Helpers\FcmGoogleHelper;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FCMService
{
    private string $fcmUrl;

    public function __construct()
    {
        $projectId      = config('services.firebase.project_id');
        $this->fcmUrl   = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
    }

    /**
     * بيبعت notification لجهاز يوزر معين
     *
     * @param User   $user  اليوزر اللي هيستقبل الـ notification
     * @param string $title عنوان الرسالة
     * @param string $body  نص الرسالة
     * @param array  $data  بيانات إضافية للـ Flutter (اختياري)
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body,
        array $data = []
    ): bool {

        // لو اليوزر معندوش token مش هنبعتله
        if (empty($user->fcm_token)) {
            Log::info("FCMService: User {$user->id} has no FCM token");
            return false;
        }

        // بنطلب Access Token جديد من Google
        $accessToken = FcmGoogleHelper::configureClient();

        if (!$accessToken) {
            Log::error("FCMService: Could not get access token");
            return false;
        }

        // بنبعت الـ notification لـ FCM API
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type'  => 'application/json',
        ])->post($this->fcmUrl, [
            'message' => [
                'token'        => $user->fcm_token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                // data بتوصل للـ Flutter في الـ background
                'data' => array_map('strval', $data),
            ],
        ]);

        if ($response->successful()) {
            Log::info("FCMService: Notification sent to user {$user->id}");
            return true;
        }

        // لو الـ token باظ أو منتهي — امسحه من DB
        if ($response->status() === 404) {
            Log::warning("FCMService: Invalid token for user {$user->id} — clearing");
            $user->update(['fcm_token' => null]);
        }

        Log::error('FCMService: Send failed', [
            'user_id'  => $user->id,
            'status'   => $response->status(),
            'response' => $response->body(),
        ]);

        return false;
    }
}