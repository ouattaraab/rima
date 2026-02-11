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
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
