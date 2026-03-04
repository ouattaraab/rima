<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // BrandSeeder, VehicleModelSeeder removed
            // — these are now populated via Excel import on the web interface
            VehicleCategorySeeder::class, // Base categories (updateOrCreate — safe with Excel imports)
            StructureSeeder::class,
            InsuranceCompanySeeder::class,
            DirectionSeeder::class,
            VehicleTypeSeeder::class,
            FuelTypeSeeder::class,
            TransmissionSeeder::class,
            VehicleStatusSeeder::class,
            ContractTypeSeeder::class,
            CoverageTypeSeeder::class,
            ColorSeeder::class,
        ]);
    }
}
