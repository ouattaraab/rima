<?php

namespace App\Http\Requests\Referential;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_id' => 'required|uuid|exists:brands,id',
            'name' => 'required|string|max:100',
            'category' => 'nullable|string|max:20',
        ];
    }
}
