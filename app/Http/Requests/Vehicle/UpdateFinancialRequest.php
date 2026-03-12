<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFinancialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isSousContrat = $this->vehicleContractType() === 'Sous contrat';

        return [
            'financing_mode' => 'required|in:Leasing,Direct',
            'bank_name' => 'nullable|string|max:50|required_if:financing_mode,Leasing',
            'contract_number' => 'nullable|string|max:50|required_if:financing_mode,Leasing',
            'withdrawal_start_date' => 'nullable|date|required_if:financing_mode,Leasing',
            'withdrawal_end_date' => 'nullable|date|after_or_equal:withdrawal_start_date|required_if:financing_mode,Leasing',
            'contract_start_date' => [
                'nullable', 'date',
                Rule::requiredIf($isSousContrat),
            ],
            'provision_date' => [
                'nullable', 'date',
                Rule::requiredIf($isSousContrat),
            ],
            'code_immo_dfc' => ['nullable', 'string', 'size:7', 'regex:/^[0-9]{7}$/'],
            'code_immo_dbcg' => ['nullable', 'string', 'size:7', 'regex:/^[0-9]{7}$/'],
            'code_equipement' => ['nullable', 'string', 'size:4', 'regex:/^[0-9]{4}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'financing_mode.required' => 'Le mode de financement est obligatoire.',
            'financing_mode.in' => 'Le mode de financement doit etre Leasing ou Direct.',
            'bank_name.required_if' => 'Le nom de la banque est obligatoire pour un financement en Leasing.',
            'contract_number.required_if' => 'Le numero de contrat est obligatoire pour un financement en Leasing.',
            'withdrawal_start_date.required_if' => 'La date de debut de prelevement est obligatoire pour un financement en Leasing.',
            'withdrawal_end_date.required_if' => 'La date de fin de prelevement est obligatoire pour un financement en Leasing.',
            'withdrawal_end_date.after_or_equal' => 'La date de fin de prelevement doit etre posterieure ou egale a la date de debut.',
            'contract_start_date.required' => 'La date de debut du contrat est obligatoire pour les vehicules sous contrat.',
            'provision_date.required' => 'La date de mise a disposition est obligatoire pour les vehicules sous contrat.',
            'code_immo_dfc.size' => 'Le code IMMO DFC doit contenir exactement 7 chiffres.',
            'code_immo_dfc.regex' => 'Le code IMMO DFC doit contenir uniquement des chiffres.',
            'code_immo_dbcg.size' => 'Le code IMMO DBCG doit contenir exactement 7 chiffres.',
            'code_immo_dbcg.regex' => 'Le code IMMO DBCG doit contenir uniquement des chiffres.',
            'code_equipement.size' => 'Le code equipement doit contenir exactement 4 chiffres.',
            'code_equipement.regex' => 'Le code equipement doit contenir uniquement des chiffres.',
        ];
    }

    private function vehicleContractType(): ?string
    {
        $vehicle = $this->route('vehicle');
        if (is_string($vehicle)) {
            $vehicle = \App\Models\Vehicle::find($vehicle);
        }
        return $vehicle?->contract_type;
    }
}
