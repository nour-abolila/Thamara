<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUserprofile(Request $request)
    {
        $user = $request->user(); 

        if (!$user) {
            return response()->json('User Not Found', 404);
        }

        return response()->json([
            'status' => 'success',
            'user_data' => new UserProfileResource($user),
        ], 200);
    }
}
