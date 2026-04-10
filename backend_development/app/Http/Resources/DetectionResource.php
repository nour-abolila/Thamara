<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'plant_name' => $this->plant_name,
            'image_url' => asset('storage/' . $this->image_path),
            'disease_name' => $this->disease_name,
            'disease_description' => $this->disease_description,
            'confidence' => round($this->confidence * 100, 2) . '%',
            'severity_level' => $this->severity_level,
            'treatment' => $this->treatment,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
