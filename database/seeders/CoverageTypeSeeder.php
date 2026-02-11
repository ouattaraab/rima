<?php

namespace Database\Seeders;

use App\Models\CoverageType;
use Illuminate\Database\Seeder;

class CoverageTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Tout risque', 'Tiers'] as $name) {
            CoverageType::updateOrCreate(['name' => $name]);
        }
    }
}
