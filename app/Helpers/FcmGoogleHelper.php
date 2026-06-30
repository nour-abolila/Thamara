<?php

namespace App\Helpers;


use Google\Client as GClient;
use Google\Service\FirebaseCloudMessaging;
use Illuminate\Support\Facades\Log;

class FcmGoogleHelper
{
    public static function configureClient(): ?string
    {
        $path = storage_path('firebase_credentials.json');

        if (!file_exists($path)) {
            Log::error('FcmGoogleHelper: Firebase credentials file not found', ['path' => $path]);
            return null;
        }

        $client = new GClient();
        try {
            $client->setAuthConfig($path);
            $client->addScope(FirebaseCloudMessaging::CLOUD_PLATFORM);
            $accessToken = self::generateToken($client);

            if (empty($accessToken) || !isset($accessToken['access_token'])) {
                Log::error('FcmGoogleHelper: Failed to obtain access token - empty response');
                return null;
            }

            $client->setAccessToken($accessToken);
            return $accessToken['access_token'];
        } catch (\Throwable $e) {
            Log::error('FcmGoogleHelper: Failed to configure Firebase client', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private static function generateToken(GClient $client): ?array
    {
        $client->fetchAccessTokenWithAssertion();
        return $client->getAccessToken();
    }
}
