<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'username' => 'admin.sodeci',
                'email' => 'admin@sodeci.ci',
                'password' => 'Admin@2026!',
                'first_name' => 'Administrateur',
                'last_name' => 'SODECI',
                'role' => 'admin_sodeci',
                'organization' => 'SODECI',
                'region' => 'Abidjan',
                'phone' => '+225 07 00 00 01',
            ],
            [
                'username' => 'supervisor.sodeci.01',
                'email' => 'supervisor@sodeci.ci',
                'password' => 'Super@2026!',
                'first_name' => 'Koffi',
                'last_name' => 'Marie',
                'role' => 'supervisor_sodeci',
                'organization' => 'SODECI',
                'region' => 'Abidjan',
                'phone' => '+225 07 00 00 02',
            ],
            [
                'username' => 'supervisor.cidec.01',
                'email' => 'supervisor@cidec.ci',
                'password' => 'Super@2026!',
                'first_name' => 'Traore',
                'last_name' => 'Ibrahim',
                'role' => 'supervisor_cidec',
                'organization' => 'CIDEC',
                'region' => 'Abidjan',
                'phone' => '+225 07 00 00 03',
            ],
            [
                'username' => 'agent.cidec.01',
                'email' => 'agent01@cidec.ci',
                'password' => 'Agent@2026!',
                'first_name' => 'Kouassi',
                'last_name' => 'Jean',
                'role' => 'agent_cidec',
                'organization' => 'CIDEC',
                'region' => 'Abidjan',
                'phone' => '+225 07 12 34 56',
            ],
            [
                'username' => 'agent.cidec.02',
                'email' => 'agent02@cidec.ci',
                'password' => 'Agent@2026!',
                'first_name' => 'Aya',
                'last_name' => 'Kouadio',
                'role' => 'agent_cidec',
                'organization' => 'CIDEC',
                'region' => 'Bouake',
                'phone' => '+225 07 11 22 33',
            ],
            [
                'username' => 'finance.dbcg.01',
                'email' => 'finance.dbcg@sodeci.ci',
                'password' => 'Finance@2026!',
                'first_name' => 'Alassane',
                'last_name' => 'Diallo',
                'role' => 'finance_dbcg',
                'organization' => 'SODECI',
                'region' => 'Abidjan',
                'phone' => '+225 07 00 00 10',
            ],
            [
                'username' => 'finance.dfc.01',
                'email' => 'finance.dfc@sodeci.ci',
                'password' => 'Finance@2026!',
                'first_name' => 'Fatou',
                'last_name' => 'Coulibaly',
                'role' => 'finance_dfc',
                'organization' => 'SODECI',
                'region' => 'Abidjan',
                'phone' => '+225 07 00 00 11',
            ],
            [
                'username' => 'validateur.sodeci.01',
                'email' => 'validateur@sodeci.ci',
                'password' => 'Validateur@2026!',
                'first_name' => 'Moussa',
                'last_name' => 'Kone',
                'role' => 'validateur_sodeci',
                'organization' => 'SODECI',
                'region' => 'Abidjan',
                'phone' => '+225 07 00 00 12',
            ],
        ];

        foreach ($users as $userData) {
            $username = $userData['username'];
            $email = $userData['email'];
            $password = $userData['password'];
            unset($userData['password']);

            // Skip if user already exists by username
            $existing = User::where('username', $username)->first();
            if ($existing) {
                $existing->update(array_merge($userData, ['password' => $password]));
                continue;
            }

            // Skip if email is already taken by another user
            $emailTaken = User::where('email', $email)->where('username', '!=', $username)->exists();
            if ($emailTaken) {
                // Use a unique email to avoid conflict
                $userData['email'] = $username . '@test.local';
            }

            User::create(array_merge($userData, ['password' => $password]));
        }
    }
}
