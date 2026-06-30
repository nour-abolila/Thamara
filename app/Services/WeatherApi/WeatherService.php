<?php
// app/Services/WeatherApi/WeatherService.php

namespace App\Services\WeatherApi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    public function getCurrentWeather($latitude, $longitude): ?array
    {
        $response = Http::get(config('services.weather.base_url') . '/current.json', [
            'key' => config('services.weather.api_key'),
            'q'   => "{$latitude},{$longitude}",
        ]);

        if ($response->failed()) {
            Log::error('WeatherService: API call failed', [
                'lat' => $latitude,
                'lng' => $longitude,
            ]);
            return null;
        }

        $data = $response->json();

        return [
            'temp'      => $data['current']['temp_c'],
            'humidity'  => $data['current']['humidity'],
            'precip_mm' => $data['current']['precip_mm'],
            'wind_kph'  => $data['current']['wind_kph'],
        ];
    }
}
