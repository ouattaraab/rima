<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
            'role' => 'agent_cidec',
            'organization' => 'CIDEC',
            'region' => 'Abidjan',
            'is_active' => true,
            'failed_login_attempts' => 0,
        ];
    }

    public function agentCidec(): static
    {
        return $this->state(fn () => [
            'role' => 'agent_cidec',
            'organization' => 'CIDEC',
        ]);
    }

    public function supervisorCidec(): static
    {
        return $this->state(fn () => [
            'role' => 'supervisor_cidec',
            'organization' => 'CIDEC',
        ]);
    }

    public function supervisorSodeci(): static
    {
        return $this->state(fn () => [
            'role' => 'supervisor_sodeci',
            'organization' => 'SODECI',
        ]);
    }

    public function adminSodeci(): static
    {
        return $this->state(fn () => [
            'role' => 'admin_sodeci',
            'organization' => 'SODECI',
        ]);
    }
}
