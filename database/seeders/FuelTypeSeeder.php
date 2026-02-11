<?php

namespace Database\Seeders;

use App\Models\FuelType;
use Illuminate\Database\Seeder;

class FuelTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Essence', 'Gasoil', 'Hybride', 'Electrique'] as $name) {
            FuelType::updateOrCreate(['name' => $name]);
        }
    }
}
