<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Toyota', 'Peugeot', 'Renault', 'Nissan', 'Mitsubishi',
            'Ford', 'Mercedes-Benz', 'Hyundai', 'Kia', 'Suzuki',
            'Honda', 'Volkswagen', 'BMW', 'Isuzu', 'Land Rover',
            'Chevrolet', 'Dacia', 'Fiat', 'Yamaha', 'Bajaj',
        ];

        foreach ($brands as $name) {
            Brand::create(['name' => $name]);
        }
    }
}
