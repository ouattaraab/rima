<?php

namespace Database\Seeders;

use App\Models\InsuranceCompany;
use Illuminate\Database\Seeder;

class InsuranceCompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            'NSIA Assurances',
            'Allianz CI',
            'AXA Assurances',
            'SAAR Assurances',
            'Saham Assurance',
            'Atlantique Assurances',
            'SUNU Assurances',
            'Colina Assurances',
            'Groupama',
            'SONAR',
        ];

        foreach ($companies as $name) {
            InsuranceCompany::create(['name' => $name]);
        }
    }
}
