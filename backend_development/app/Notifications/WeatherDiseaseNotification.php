<?php
// app/Notifications/WeatherDiseaseNotification.php

namespace App\Notifications;

class WeatherDiseaseNotification
{
    public string  $title;
    public string  $body;
    public string  $type;
    public ?string $id;

    public function __construct(
        string  $title,
        string  $body,
        string  $type = 'weather_alert',
        ?string $id   = null
    ) {
        $this->title = $title;
        $this->body  = $body;
        $this->type  = $type;
        $this->id    = $id;
    }
}