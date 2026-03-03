<?php

namespace App\Http\Requests\Vehicle;

use App\Models\VehicleCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_type' => 'required|in:Auto,Moto',
            'category' => ['required', Rule::in(VehicleCategory::where('is_active', true)->pluck('name')->toArray())],
            'brand' => 'required|string|max:50',
            'model' => 'required|string|max:100',
            'version' => [
                'nullable', 'string', 'max:50',
                Rule::prohibitedIf(fn() => $this->input('category') !== 'Berline'),
            ],
            'commissioning_date' => 'required|date|before_or_equal:today',
            'contract_type' => 'required|in:Sous contrat,Flotte',
            'color' => 'required|string|max:30|in:Blanc,Noir,Gris,Bleu,Rouge,Vert,Jaune,Beige,Marron,Autre',

            'registration_number' => [
                'nullable', 'string', 'max:10', 'regex:/^[A-Z0-9\s\-]+$/i',
                Rule::unique('vehicles')->ignore($this->route('vehicle')),
                'required_without:temporary_registration',
            ],
            'temporary_registration' => 'nullable|string|max:30|regex:/^[A-Z0-9\s\-]+$/i|required_without:registration_number',
            'chassis_number' => 'nullable|string|max:30|regex:/^[A-Z0-9]+$/i',
            'chassis_readable' => 'required|boolean',

            'fuel_type' => 'required|in:Essence,Gasoil,Hybride,Electrique',
            'transmission' => [
                'nullable', 'in:Automatique,Manuelle',
                Rule::requiredIf(fn() => $this->input('vehicle_type') === 'Auto'),
                Rule::prohibitedIf(fn() => $this->input('vehicle_type') === 'Moto'),
            ],
            'engine_displacement' => 'nullable|integer|min:50|max:99999',
            'fiscal_power' => 'nullable|integer|min:1|max:999',
            'seats_count' => [
                'required', 'integer', 'min:1',
                'max:' . ($this->input('vehicle_type') === 'Moto' ? 2 : ($this->input('category') === 'Camion' ? 10 : 7)),
            ],
            'load_capacity' => [
                'nullable', 'integer', 'min:1', 'max:99999',
                Rule::requiredIf(fn() => in_array($this->input('category'), ['Camion', 'Pick-up'])),
            ],
            'mileage' => 'required|integer|min:1|max:9999999',

            'status' => 'required|in:En service,En reparation,Reforme,Cede',
            'structure_ci' => [
                'nullable', 'string', 'max:10',
                Rule::requiredIf(fn() => in_array($this->input('status'), ['En service', 'En reparation'])),
            ],
            'has_roll_bars' => [
                'nullable', 'boolean',
                Rule::requiredIf(fn() => $this->input('category') === 'Pick-up'),
            ],
            'cabin_type' => [
                'nullable', 'in:Simple cabine,Double cabine',
                Rule::requiredIf(fn() => $this->input('category') === 'Pick-up'),
            ],
            'special_equipment' => [
                'nullable', 'string', 'max:100',
                Rule::prohibitedIf(fn() => $this->input('category') !== 'Camion'),
            ],

            'technical_inspection_date' => 'required|date|before_or_equal:today|after_or_equal:commissioning_date',

            'is_insured' => [
                'required', 'boolean',
                function (string $attribute, mixed $value, \Closure $fail) {
                    // CDC: Assurance obligatoire si vehicule "En service"
                    if ($this->input('status') === 'En service' && !$value) {
                        $fail('L\'assurance est obligatoire pour les vehicules en service.');
                    }
                },
            ],
            'insurance_company' => 'nullable|string|max:50|required_if:is_insured,true',
            'policy_number' => 'nullable|string|max:30|required_if:is_insured,true',
            'coverage_type' => 'nullable|string|max:30',
            'insurance_start_date' => 'nullable|date|required_if:is_insured,true|after_or_equal:commissioning_date',
            'insurance_end_date' => 'nullable|date|after:insurance_start_date|required_if:is_insured,true',

            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
            'gps_accuracy' => 'nullable|numeric|min:0|max:1000',

            // Multi-conducteurs (V1.5)
            'drivers' => 'required_without:user_matricule|array|min:1',
            'drivers.*.direction' => 'required_with:drivers|string|max:100',
            'drivers.*.matricule' => 'required_with:drivers|string|size:7|regex:/^[A-Z0-9]{7}$/i',
            'drivers.*.driver_license' => 'required_with:drivers|string|max:50',
            'drivers.*.is_primary' => 'sometimes|boolean',

            // @deprecated — Ancien format single-driver (retrocompat)
            'user_direction' => 'required_without:drivers|string|max:100',
            'user_matricule' => 'required_without:drivers|string|size:7|regex:/^[A-Z0-9]{7}$/i',
            'user_driver_license' => 'required_without:drivers|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'registration_number.required_without' => 'Le numero d\'immatriculation ou l\'immatriculation temporaire est obligatoire.',
            'registration_number.max' => 'L\'immatriculation ne doit pas depasser 10 caracteres.',
            'registration_number.regex' => 'L\'immatriculation ne doit contenir que des lettres, chiffres, espaces et tirets.',
            'temporary_registration.required_without' => 'L\'immatriculation temporaire ou le numero d\'immatriculation est obligatoire.',
            'chassis_number.regex' => 'Le numero de chassis ne doit contenir que des lettres et chiffres.',
            'transmission.required' => 'La transmission est obligatoire pour les vehicules de type Auto.',
            'structure_ci.required' => 'Le centre d\'imputation est obligatoire pour les vehicules en service ou en reparation.',
            'has_roll_bars.required' => 'L\'indication des arceaux est obligatoire pour les Pick-up.',
            'cabin_type.required' => 'Le type de cabine est obligatoire pour les Pick-up.',
            'cabin_type.in' => 'Le type de cabine doit etre Simple cabine ou Double cabine.',
            'load_capacity.required' => 'La charge utile est obligatoire pour les Camions et Pick-up.',
            'mileage.min' => 'Le kilometrage doit etre strictement positif.',
            'seats_count.min' => 'Le nombre de places doit etre superieur a 0.',
            'seats_count.max' => 'Le nombre de places ne peut pas depasser :max pour cette categorie.',
            'color.in' => 'La couleur doit etre : Blanc, Noir, Gris, Bleu, Rouge, Vert, Jaune, Beige, Marron ou Autre.',
            'special_equipment.prohibited' => 'Les equipements speciaux ne concernent que les Camions.',
            'transmission.prohibited' => 'La transmission n\'est pas applicable pour les Motos.',
            'version.prohibited' => 'La version ne concerne que les Berlines.',
            'insurance_company.required_if' => 'La compagnie d\'assurance est obligatoire si le vehicule est assure.',
            'policy_number.required_if' => 'Le numero de police est obligatoire si le vehicule est assure.',
            'insurance_end_date.after' => 'La date de fin d\'assurance doit etre posterieure a la date de debut.',
            'technical_inspection_date.after_or_equal' => 'La date de controle technique ne peut pas etre anterieure a la mise en circulation.',
            'insurance_start_date.after_or_equal' => 'La date de debut d\'assurance ne peut pas etre anterieure a la mise en circulation.',
            'user_direction.required' => 'La direction de l\'utilisateur est obligatoire.',
            'user_matricule.required' => 'Le matricule de l\'utilisateur est obligatoire.',
            'user_matricule.size' => 'Le matricule doit comporter exactement 7 caracteres.',
            'user_matricule.regex' => 'Le matricule ne doit contenir que des lettres et chiffres.',
            'user_driver_license.required' => 'Le numero de permis de conduire est obligatoire.',
        ];
    }
}
