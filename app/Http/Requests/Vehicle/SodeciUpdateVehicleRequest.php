<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class SodeciUpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'modification_reason' => 'required|string|max:500',
            'fields_to_update' => 'required|array|min:1',
            'fields_to_update.registration_number' => 'sometimes|string|max:20',
            'fields_to_update.mileage' => 'sometimes|integer|min:0',
            'fields_to_update.status' => 'sometimes|in:En service,En reparation,Reforme,Cede',
            'fields_to_update.insurance_company' => 'sometimes|string|max:100',
            'fields_to_update.policy_number' => 'sometimes|string|max:30',
            'fields_to_update.insurance_start_date' => 'sometimes|date',
            'fields_to_update.insurance_end_date' => 'sometimes|date',
            // Section 5.7 - V1.4
            'fields_to_update.user_direction' => 'sometimes|string|max:100',
            'fields_to_update.user_matricule' => 'sometimes|string|size:7',
            'fields_to_update.user_driver_license' => 'sometimes|string|max:50',
        ];
    }
}
