<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class RejectVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => 'required|in:photo_issue,registration_error,data_inconsistency,missing_information,other',
            'rejection_comment' => 'required|string|min:10|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_comment.required' => 'Le commentaire de rejet est obligatoire (rejet motive).',
            'rejection_comment.min' => 'Le commentaire de rejet doit comporter au moins 10 caracteres.',
        ];
    }
}
