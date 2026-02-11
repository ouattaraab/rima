<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Auto', 'Moto'] as $name) {
            VehicleType::updateOrCreate(['name' => $name]);
        }
    }
}
