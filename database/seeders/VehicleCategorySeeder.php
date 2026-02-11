<?php

namespace Database\Seeders;

use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;

class VehicleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Utilitaire', 'vehicle_type' => 'Auto'],
            ['name' => 'Berline', 'vehicle_type' => 'Auto'],
            ['name' => 'Pick-up', 'vehicle_type' => 'Auto'],
            ['name' => 'Camion', 'vehicle_type' => 'Auto'],
            ['name' => 'Moto', 'vehicle_type' => 'Moto'],
        ];

        foreach ($items as $item) {
            VehicleCategory::updateOrCreate(
                ['name' => $item['name'], 'vehicle_type' => $item['vehicle_type']],
                $item
            );
        }
    }
}
