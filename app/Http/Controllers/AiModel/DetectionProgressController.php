<?php

namespace App\Http\Controllers\AiModel;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiModel\DetectionProgressRequest;
use App\Http\Resources\DetectionProgressResource;
use App\Models\Detection;
use App\Models\DetectionProgress;
use Google\Service\CustomerEngagementSuite\ApiAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Prompts\Progress;

class DetectionProgressController extends Controller
{
    public function storeScan(DetectionProgressRequest $request, Detection $detection)
    {
        if ($detection->user_id !== Auth::id()) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $path = $request->file('image')->store('detectionscans', 'public');

        $progress = DetectionProgress::create([
            ...$request->validated(),
            'detection_id' => $detection->id,
            'image_path'   => $path,
        ]);

        return ApiResponse::success(
            'Progress saved successfully',
            ['data' => new DetectionProgressResource($progress)],
            201
        );
    }



    public function getScan(Detection $detection)
    {
        if ($detection->user_id !== Auth::id()) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $scans = $detection->detectionProgress;

        return ApiResponse::success(
            'Scan history retrieved successfully',
            [
                'scans' => DetectionProgressResource::collection($scans),
            ]
        );
    }
}
