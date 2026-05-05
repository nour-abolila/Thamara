<?php

namespace App\Services\Notification;

use App\Helpers\FcmGoogleHelper;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NotificationSender
{
    private Client $client;
    private array $headers;
    private string $projectId;
    private string $url;

    public function __construct()
    {
        $this->projectId = 'thamara-fc885';
        $this->setUrl();
        $this->setClient();
        $this->setHeaders();
    }

    /* ------------------------------------------------------------
     | Setup
     |------------------------------------------------------------ */

    private function setClient(): void
    {
        $this->client = new Client([
            'timeout' => 5,
            'connect_timeout' => 3,
        ]);
    }

    private function setHeaders(): void
    {
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type'  => 'application/json',
        ];
    }

    private function setUrl(): void
    {
        $this->url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
    }

    private function getAccessToken(): string
    {
        return Cache::remember('fcm_access_token', 3500, function () {
            return FcmGoogleHelper::configureClient();
        });
    }

    /* ------------------------------------------------------------
     | Public API
     |------------------------------------------------------------ */

    public function sendNotification(object $notification, array $deviceTokens): array
    {
        $deviceTokens = array_values(array_filter($deviceTokens));
        $url = $this->url;

        $requests = function () use ($deviceTokens, $notification, $url) {
            foreach ($deviceTokens as $index => $token) {
                yield $index => new Request(
                    'POST',
                    $url,
                    $this->headers,
                    json_encode($this->buildPayload($notification, $token))
                );
            }
        };

        $successfulTokens = [];
        $failedTokens = [];

        $pool = new Pool($this->client, $requests(), [
            'concurrency' => 50,

            'fulfilled' => function ($response, $index) use (
                &$successfulTokens,
                &$failedTokens,
                $deviceTokens
            ) {
                $body = json_decode((string) $response->getBody(), true);

                if ($response->getStatusCode() === 200 && isset($body['name'])) {
                    $successfulTokens[] = $deviceTokens[$index];
                } else {
                    $this->handleFailure($body, $deviceTokens[$index], $failedTokens);
                }
            },

            'rejected' => function ($reason, $index) use (&$failedTokens, $deviceTokens) {
                $failedTokens[$deviceTokens[$index]] =
                    $reason instanceof Exception
                        ? $reason->getMessage()
                        : 'Request rejected';
            },
        ]);

        $pool->promise()->wait();

        Log::info('FCM Notification Result', [
            'successful' => count($successfulTokens),
            'failed'     => count($failedTokens),
        ]);

        return [
            'successful'       => count($successfulTokens),
            'failed'           => count($failedTokens),
            'successfulTokens' => $successfulTokens,
            'failedTokens'     => $failedTokens,
        ];
    }

    /* ------------------------------------------------------------
     | Helpers
     |------------------------------------------------------------ */

    private function buildPayload(object $notification, string $token): array
    {
        return [
            'message' => [
                'token' => $token,

                'notification' => [
                    'title' => $notification->title,
                    'body'  => $notification->body,
                    'image' => null
                      
                ],

                'data' => [
                    'id'   => (string) ($notification->id ?? ''),
                    'type' => (string) ($notification->type ?? 'default'),
                ],

                'android' => [
                    'notification' => [
                        'sound' => 'default',
                    ],
                ],

                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function handleFailure(array $body, string $token, array &$failedTokens): void
    {
        $error = $body['error'] ?? null;

        $failedTokens[$token] = $error['message'] ?? json_encode($body);

        // 🔥 IMPORTANT: Remove invalid tokens
        if (
            isset($error['status']) &&
            in_array($error['status'], ['NOT_FOUND', 'UNREGISTERED'])
        ) {
            $this->deleteInvalidToken($token);
        }
    }

    private function deleteInvalidToken(string $token): void
    {
        // مثال:
        // DeviceToken::where('token', $token)->delete();

        Log::warning('FCM invalid token removed', [
            'token' => $token,
        ]);
    }
}