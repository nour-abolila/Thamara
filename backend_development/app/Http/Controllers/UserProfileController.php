<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\User\UpdateUserProfileRequest;
use App\Http\Resources\UserProfileResource;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    /**
     * عرض بيانات اليوزر الحالي
     */
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

    /**
     * تحديث بيانات اليوزر الحالي
     */
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

    /**
     * حذف حساب اليوزر الحالي
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        $user->delete();

        return ApiResponse::success(
            'User profile deleted successfully.'
        );
    }
}