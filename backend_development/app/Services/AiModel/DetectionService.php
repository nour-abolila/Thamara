<?php

namespace App\Services\AiModel;

use App\Models\Detection;

class DetectionService
{
  // Store the detection model ai result in the database
  public function storedetection($request, $user)
  {
    $path = request()->file('image')->store('detections', 'public');

    Detection::create([
      'user_id' => $user->id,
      'plant_name' => $request->plant_name,
      'image_path' => $path,
      'disease_name' => $request->disease_name,
      'disease_description' => $request->disease_description,
      'confidence' => $request->confidence,
      'severity_level' => $request->severity_level,
      'treatment' => $request->treatment,
    ]);
  }


  // Get all detections for a specific user
  public function getUserDetections($user)
  {
    return $user->detections()->latest()->get(); // Get detections for the authenticated user, ordered by latest
  }
}
