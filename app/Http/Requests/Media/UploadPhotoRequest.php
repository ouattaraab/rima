<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UploadPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => 'required|uuid|exists:vehicles,id',
            'photo_type' => 'required|in:front,rear,side,additional,registration_card,chassis,driver_license,registration_plate,temp_registration_plate',
            'file' => 'required|file|mimes:jpeg,jpg,png|max:10240',
            'comment' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'La photo ne doit pas depasser 10 Mo.',
            'file.mimes' => 'La photo doit etre au format JPEG ou PNG.',
            'vehicle_id.exists' => 'Le vehicule specifie n\'existe pas.',
        ];
    }
}
