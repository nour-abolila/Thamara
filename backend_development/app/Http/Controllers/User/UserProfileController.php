<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserProfileRequest;
use App\Http\Resources\UserProfileResource;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{

    public function show(Request $request)
    {
        $user = $request->user();

        return ApiResponse::success(
            'User profile retrieved successfully.',
            [
                'user' => new UserProfileResource($user),
            ]
        );
    }



    public function update(UpdateUserProfileRequest $request)
    {
        $user = $request->user();

        $user->update($request->validated());

        return ApiResponse::success(
            'User profile updated successfully.',
            [
                'user' => new UserProfileResource($user->fresh()),
            ]
        );
    }



    public function destroy(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        $user->delete();

        return ApiResponse::success(
            'User profile deleted successfully.'
        );
    }
}
