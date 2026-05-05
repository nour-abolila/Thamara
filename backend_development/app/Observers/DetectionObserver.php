<?php
// app/Observers/DetectionObserver.php

namespace App\Observers;

use App\Models\Detection;
use App\Notifications\WeatherDiseaseNotification;
use App\Services\Notification\NotificationSender;
use App\Services\WeatherApi\WeatherService;
use Illuminate\Support\Facades\Log;

class DetectionObserver
{
    public function __construct(
        private WeatherService     $weatherService,
        private NotificationSender $notificationSender
    ) {}

    /**
     * بيتفعل تلقائياً لما detection جديد يتحفظ في الـ DB
     */
    public function created(Detection $detection): void
    {
        info('ai');
        $user = $detection->user;

        // لو اليوزر معندوش token أو location — وقف
        if (!$user?->fcm_token || !$user?->latitude || !$user?->longitude) {
            Log::info('DetectionObserver: skipped — missing token or location', [
                'user_id' => $user?->id,
            ]);
            return;
        }

        // لو النبات healthy — مفيش داعي نبعت تحذير
        if (str_contains(strtolower($detection->disease_name), 'healthy')) {
            return;
        }

        // جيب الطقس الحالي
        $weather = $this->weatherService->getCurrentWeather(
            $user->latitude,
            $user->longitude
        );

        if (!$weather) {
            Log::warning('DetectionObserver: weather fetch failed', [
                'user_id'      => $user->id,
                'detection_id' => $detection->id,
            ]);
            return;
        }

        // شوف لو في تحذير
        $alert = $this->evaluate(
            $detection->disease_name,
            $detection->plant_name,
            $weather
        );

        if (!$alert) {
            Log::info('DetectionObserver: no alert triggered', [
                'disease' => $detection->disease_name,
                'weather' => $weather,
            ]);
            return;
        }

        // ابعت الـ notification
        $notification = new WeatherDiseaseNotification(
            title: $alert['title'],
            body:  $alert['body'],
            type:  'weather_alert',
            id:    (string) $detection->id
        );

        $this->notificationSender->sendNotification(
            $notification,
            [$user->fcm_token]
        );

        Log::info('DetectionObserver: alert sent', [
            'user_id'      => $user->id,
            'detection_id' => $detection->id,
            'disease'      => $detection->disease_name,
        ]);
    }

    // -------------------------------------------------------
    // الـ if conditions بناءً على الـ JSON file
    // -------------------------------------------------------
    private function evaluate(
        string $diseaseName,
        string $plantName,
        array  $weather
    ): ?array {

        $temp    = $weather['temp'];
        $humidity = $weather['humidity'];
        $precip  = $weather['precip_mm'];
        $wind    = $weather['wind_kph'];
        $disease = strtolower(trim($diseaseName));

        // ============================================
        // MANGO
        // ============================================

        if ($disease === 'mango_anthracnose') {
            if ($precip > 5 && $humidity > 85) {
                return $this->alert($plantName,
                    "مطر ورطوبة {$humidity}% — خطر Anthracnose مباشر. رشّ Mancozeb الآن"
                );
            }
            if ($humidity > 85) {
                return $this->alert($plantName,
                    "رطوبة عالية {$humidity}% تسرّع Anthracnose — حسّن التهوية وتجنب الري العلوي"
                );
            }
            if ($temp >= 24 && $temp <= 28 && $humidity > 70) {
                return $this->alert($plantName,
                    "حرارة {$temp}°م ورطوبة {$humidity}% مثالية لـ Anthracnose — ابدأ برنامج الوقاية"
                );
            }
        }

        if ($disease === 'mango_powdery_mildew') {
            if ($temp >= 20 && $temp <= 27 && $humidity >= 40 && $humidity <= 60) {
                return $this->alert($plantName,
                    "حرارة {$temp}°م ورطوبة {$humidity}% مثالية لـ Powdery Mildew — رشّ كبريت كل 7-10 أيام"
                );
            }
        }

        if ($disease === 'mango_bacterial_canker') {
            if ($precip > 2 && $wind > 25) {
                return $this->alert($plantName,
                    "مطر مع رياح {$wind} كم/س — خطر Bacterial Canker. افحص الأفرع بعد العاصفة"
                );
            }
            if ($humidity > 80) {
                return $this->alert($plantName,
                    "رطوبة {$humidity}% تبقّي البكتيريا حية — عقّم الأدوات وتجنب الري العلوي"
                );
            }
        }

        if ($disease === 'mango_die_back') {
            if ($temp > 32 && $humidity < 40) {
                return $this->alert($plantName,
                    "حرارة {$temp}°م مع جفاف — خطر Die Back شديد. زوّد الري وقلّم الأفرع المريضة"
                );
            }
        }

        if ($disease === 'mango_sooty_mould') {
            if ($humidity > 80) {
                return $this->alert($plantName,
                    "رطوبة {$humidity}% تسرّع Sooty Mould — عالج الحشرات بزيت النيم وحسّن التهوية"
                );
            }
        }

        if ($disease === 'mango_gall_midge') {
            if ($precip > 10 && $temp >= 20 && $temp <= 28) {
                return $this->alert($plantName,
                    "مطر وحرارة {$temp}°م ستحفّز أوراق جديدة — افحص بعد 3-5 أيام لـ Gall Midge"
                );
            }
        }

        if ($disease === 'mango_cutting_weevil') {
            if ($temp > 20 && $precip > 10) {
                return $this->alert($plantName,
                    "مطر وحرارة {$temp}°م تنشّط Cutting Weevil — افحص البراعم الجديدة أسبوعياً"
                );
            }
        }

        // ============================================
        // CITRUS
        // ============================================

        if ($disease === 'citrus_black_spot') {
            if ($humidity > 80 && $precip > 5) {
                return $this->alert($plantName,
                    "رطوبة {$humidity}% مع مطر — خطر Black Spot. رشّ نحاس أو Strobilurin قبل المطر"
                );
            }
            if ($temp >= 22 && $temp <= 28) {
                return $this->alert($plantName,
                    "حرارة {$temp}°م مثالية لـ Black Spot — راقب أسبوعياً وزوّد الرش مايو-سبتمبر"
                );
            }
        }

        if ($disease === 'citrus_canker') {
            if ($precip > 2 && $wind > 25) {
                return $this->alert($plantName,
                    "مطر مع رياح {$wind} كم/س تنشر Citrus Canker — افحص النبات بعد العاصفة فوراً"
                );
            }
            if ($humidity > 75) {
                return $this->alert($plantName,
                    "رطوبة {$humidity}% تساعد بكتيريا Canker — تجنب الري العلوي وقلّم الأخشاب المصابة"
                );
            }
        }

        if ($disease === 'citrus_melanose') {
            if ($precip > 5 && $humidity > 70) {
                return $this->alert($plantName,
                    "مطر ورطوبة {$humidity}% تنشر Melanose — قلّم الأخشاب الميتة ورشّ نحاس خلال تطور الثمار"
                );
            }
        }

        if ($disease === 'citrus_greening') {
            if ($temp >= 18 && $temp <= 30 && $humidity > 70) {
                return $this->alert($plantName,
                    "حرارة {$temp}°م ورطوبة {$humidity}% مثالية لحشرة Psyllid ناقلة Greening — راقب النمو الجديد يومياً"
                );
            }
        }

        if ($disease === 'citrus_foliage_damage') {
            if ($temp > 32 && $humidity < 40) {
                return $this->alert($plantName,
                    "حرارة {$temp}°م وجفاف — إجهاد حراري يزيد تلف الأوراق. زوّد الري في الصباح الباكر"
                );
            }
        }

        // return null;
        // default — الطقس مش في نطاق الخطر بس المرض موجود
        return $this->alert($plantName,
            "تم اكتشاف {$diseaseName} في نباتك — راقب النبات بانتظام وتابع التعليمات في التطبيق"
        );
    }

    // -------------------------------------------------------
    private function alert(string $plantName, string $message): array
    {
        return [
            'title' => "⚠️ تنبيه: {$plantName}",
            'body'  => $message,
        ];
    }
}