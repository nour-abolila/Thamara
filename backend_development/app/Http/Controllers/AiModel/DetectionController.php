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
    // Dependency injection 
    public function __construct(protected DetectionService $detectionService) {}


    public function storeUserDetection(StoreDetectionRequest $request)
    {
        $user = Auth::user();

        $detection = $this->detectionService->storedetection($request, $user);

        return ApiResponse::success(
            'Detection stored successfully',
            [],
        );
    }



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



    public function deleteUserDetection($id)
    {
        $user = Auth::user();

        $this->detectionService->deleteUserDetection($user, $id);

        return ApiResponse::success(
            'Detection deleted successfully.'
        );
    }
}
