<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:50',
            'password' => 'required|string',
            'device_info' => 'nullable|array',
            'device_info.device_id' => 'nullable|string',
            'device_info.device_name' => 'nullable|string',
            'device_info.os' => 'nullable|string',
            'device_info.app_version' => 'nullable|string',
        ];
    }
}
