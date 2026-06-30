<?php

namespace App\Http\Requests\AiModel;

use Illuminate\Foundation\Http\FormRequest;

class DetectionProgressRequest extends FormRequest
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
            'image' => 'required|image|mimes:jpg,jpeg,png,webp',
            'progress_status' => 'required|string|in:Improving,Worsening,Healed,Stable,Unable to measure,',
            'confidence_level' => 'required|string',
            'progress_level' => 'required|integer|min:0|max:100',
        ];
    }
}
