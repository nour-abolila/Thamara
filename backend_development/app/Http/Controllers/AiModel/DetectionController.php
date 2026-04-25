<?php

namespace App\Http\Controllers\AiModel;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiModel\StoreDetectionRequest;
use App\Http\Resources\DetectionResource;
use App\Services\AiModel\DetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetectionController extends Controller
{
    // Dependency injection of the DetectionService
    public function __construct(protected DetectionService $detectionService) {}


    // Store the detection result in the database
    public function storeUserDetection(StoreDetectionRequest $request)
    {
        $user = Auth::user(); // Get the authenticated user
        $detection = $this->detectionService->storedetection($request, $user);

        return ApiResponse::success(
            'Detection stored successfully',
            [],
        );
    }


    // Get all detections for the authenticated user
    public function getUserDetections(Request $request, $id = null)
    {
        $user = Auth::user();
        $detections = $this->detectionService->getUserDetections($user, $id);

        return ApiResponse::success(
            'Detections retrieved successfully',
            ['data' => DetectionResource::collection($detections)],
            200
        );
    }
}
