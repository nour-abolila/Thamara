<?php

namespace App\Services\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthService
{
    public function socialLogin(array $data)
    {
        $provider = $data['provider'];

        $accessToken = $data['access_token'];

        $providerUser = Socialite::driver($provider)->stateless()->userFromToken($accessToken);

        $socialAccount = SocialAccount::where('provider', $provider)->where('provider_id', $providerUser->getId())->first();

        if ($socialAccount) {

            $user = $socialAccount->user;
        } else {

            $user = $this->findOrCreateUser($providerUser);
        }

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $providerUser->getId(),
        ]);

        $user->update([
            'fcm_token' => $data['fcm_token'] ?? $user->fcm_token,
            'latitude'  => $data['latitude']  ?? $user->latitude,
            'longitude' => $data['longitude'] ?? $user->longitude,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'         => $user,
            'access_token' => $token,
        ];
    }



    private function findOrCreateUser($providerUser): User
    {
        $user = User::where('email', $providerUser->getEmail())->first();

        if (!$user) {

            $nameParts = explode(' ', $providerUser->getName() ?? '', 2);

            $user = User::create([
                'first_name'        => $nameParts[0] ?? 'User',
                'last_name'         => $nameParts[1] ?? '',
                'email'             => $providerUser->getEmail(),
                'password'          => null,
                'email_verified_at' => now(),
            ]);
        } else {

            if (!$user->email_verified_at) {

                $user->update(['email_verified_at' => now()]);
            }
        }

        return $user;
    }
}
