<?php

namespace App\Http\Requests\Vehicle;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vehicle = $this->route('vehicle') ? Vehicle::find($this->route('vehicle')) : null;
        $category = $this->input('category', $vehicle?->category);
        $vehicleType = $this->input('vehicle_type', $vehicle?->vehicle_type);
        $status = $this->input('status', $vehicle?->status);
        $isInsured = $this->has('is_insured') ? $this->boolean('is_insured') : $vehicle?->is_insured;

        return [
            'vehicle_type' => 'sometimes|in:Auto,Moto',
            'category' => 'sometimes|in:Utilitaire,Berline,Pick-up,Camion,Moto',
            'brand' => 'sometimes|string|max:50',
            'model' => 'sometimes|string|max:100',
            'version' => [
                'nullable', 'string', 'max:50',
                Rule::prohibitedIf(fn() => $category !== 'Berline'),
            ],
            'commissioning_date' => 'sometimes|date|before_or_equal:today',
            'contract_type' => 'sometimes|in:Sous contrat,Flotte',
            'color' => 'sometimes|string|max:30|in:Blanc,Gris,Noir,Autre',

            'registration_number' => [
                'nullable', 'string', 'max:10', 'regex:/^[A-Z0-9\s\-]+$/i',
                Rule::unique('vehicles')->ignore($this->route('vehicle')),
            ],
            'temporary_registration' => 'nullable|string|max:10|regex:/^[A-Z0-9\s\-]+$/i',
            'chassis_number' => 'nullable|string|max:30|regex:/^[A-Z0-9]+$/i',
            'chassis_readable' => 'sometimes|boolean',

            'fuel_type' => 'sometimes|in:Essence,Gasoil,Hybride,Electrique',
            'transmission' => [
                'nullable', 'in:Automatique,Manuelle',
                Rule::requiredIf(fn() => $vehicleType === 'Auto' && $this->has('vehicle_type')),
            ],
            'engine_displacement' => 'nullable|integer|min:50|max:99999',
            'seats_count' => 'sometimes|integer|min:1|max:99',
            'load_capacity' => [
                'nullable', 'integer', 'min:1', 'max:99999',
                Rule::requiredIf(fn() => in_array($category, ['Camion', 'Pick-up']) && $this->has('category')),
            ],
            'mileage' => 'sometimes|integer|min:1|max:9999999',

            'status' => 'sometimes|in:En service,En reparation,Reforme,Cede',
            'structure_ci' => [
                'nullable', 'string', 'max:10',
                Rule::requiredIf(fn() => in_array($status, ['En service', 'En reparation']) && $this->has('status')),
            ],
            'has_roll_bars' => [
                'nullable', 'boolean',
                Rule::requiredIf(fn() => $category === 'Pick-up' && $this->has('category')),
            ],
            'special_equipment' => 'nullable|string|max:100',

            'technical_inspection_date' => 'sometimes|date|before_or_equal:today',

            'is_insured' => 'sometimes|boolean',
            'insurance_company' => [
                'nullable', 'string', 'max:50',
                Rule::requiredIf(fn() => $isInsured === true),
            ],
            'policy_number' => [
                'nullable', 'string', 'max:30',
                Rule::requiredIf(fn() => $isInsured === true),
            ],
            'coverage_type' => 'nullable|string|max:30',
            'insurance_start_date' => [
                'nullable', 'date',
                Rule::requiredIf(fn() => $isInsured === true),
            ],
            'insurance_end_date' => [
                'nullable', 'date', 'after:insurance_start_date',
                Rule::requiredIf(fn() => $isInsured === true),
            ],

            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
            'gps_accuracy' => 'nullable|numeric|min:0|max:1000',

            // Section 5.7 - V1.4 : Identification utilisateur
            'user_direction' => 'sometimes|string|max:100',
            'user_matricule' => 'sometimes|string|size:7|regex:/^[A-Z0-9]{7}$/i',
            'user_driver_license' => 'sometimes|string|max:50',
        ];
    }
}
