<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'username' => ['sometimes', 'string', 'max:50', Rule::unique('users')->ignore($userId)],
            'email' => ['sometimes', 'email', 'max:100', Rule::unique('users')->ignore($userId)],
            'password' => 'sometimes|string|min:8|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/|regex:/[!@#$%^&*]/',
            'role' => 'sometimes|in:agent_cidec,supervisor_cidec,supervisor_sodeci,admin_sodeci,finance_dbcg,finance_dfc,validateur_sodeci',
            'organization' => 'sometimes|in:CIDEC,SODECI',
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'phone' => 'nullable|string|max:20',
            'region' => 'sometimes|string|max:50',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
