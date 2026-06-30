<?php

namespace App\Http\Requests\AiModel;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plant_name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png',
            'disease_name' => 'required|string|max:255',
            'disease_description' => 'required|string',
            'confidence' => 'required|numeric|min:0|max:1',
            'severity_level' => 'required|string|max:50',
            'treatment' => 'required|string',
        ];
    }
}
