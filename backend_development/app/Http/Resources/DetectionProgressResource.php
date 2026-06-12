<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetectionProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'scan_id' => $this->id,
            'image_url' => asset('storage/' . $this->image_path),
            'progress_status' => $this->progress_status,
            'progress_level' => $this->progress_level,
            'confidence_level' => $this->confidence_level,
            'scanned_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
