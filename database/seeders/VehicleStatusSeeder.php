<?php

namespace Database\Seeders;

use App\Models\VehicleStatus;
use Illuminate\Database\Seeder;

class VehicleStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['En service', 'En reparation', 'Reforme', 'Cede'] as $name) {
            VehicleStatus::updateOrCreate(['name' => $name]);
        }
    }
}
