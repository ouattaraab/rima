<?php

namespace Database\Seeders;

use App\Models\Transmission;
use Illuminate\Database\Seeder;

class TransmissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Automatique', 'Manuelle'] as $name) {
            Transmission::updateOrCreate(['name' => $name]);
        }
    }
}
