<?php

namespace Database\Seeders;

use App\Models\ContractType;
use Illuminate\Database\Seeder;

class ContractTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Sous contrat', 'Flotte'] as $name) {
            ContractType::updateOrCreate(['name' => $name]);
        }
    }
}
