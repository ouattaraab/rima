<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => [
                'required', 'string', 'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*]/',
            ],
            'role' => 'required|in:agent_cidec,supervisor_cidec,supervisor_sodeci,admin_sodeci',
            'organization' => 'required|in:CIDEC,SODECI',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'region' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractere special (!@#$%^&*).',
        ];
    }
}
