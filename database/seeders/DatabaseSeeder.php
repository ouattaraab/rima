<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BrandSeeder::class,
            VehicleModelSeeder::class,
            StructureSeeder::class,
            InsuranceCompanySeeder::class,
            DirectionSeeder::class,
            // FormOptionSeeder removed - replaced by individual referential seeders
            VehicleTypeSeeder::class,
            VehicleCategorySeeder::class,
            FuelTypeSeeder::class,
            TransmissionSeeder::class,
            VehicleStatusSeeder::class,
            ContractTypeSeeder::class,
            CoverageTypeSeeder::class,
            ColorSeeder::class,
        ]);
    }
}
