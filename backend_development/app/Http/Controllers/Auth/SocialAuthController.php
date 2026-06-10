<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Requests\Auth\SocialLoginRequest;
use App\Http\Resources\UserLoginResource;
use App\Services\Auth\SocialAuthService;

class SocialAuthController extends AuthController
{
    public function __construct(protected SocialAuthService $socialAuthService) {}

    public function socialLogin(SocialLoginRequest $request)
    {
        try {
            $result = $this->socialAuthService->socialLogin($request->validated());

            return ApiResponse::success('logged in successfully', [
                'user' => new UserLoginResource($result['user']),
                'access_token' => $result['access_token'],
            ]);
        } catch (\Exception $e) {

            return ApiResponse::error('Invalid or expired social token', [], 401);
        }
    }
}
