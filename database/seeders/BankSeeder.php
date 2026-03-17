<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            'SGBCI (Société Générale CI)',
            'BICICI (BNP Paribas)',
            'Ecobank CI',
            'NSIA Banque',
            'Banque Atlantique CI',
            'Société Ivoirienne de Banque (SIB)',
            'Orabank CI',
            'Bridge Bank CI',
            'BOA CI (Bank of Africa)',
            'BACI (Banque de l\'Habitat)',
            'Coris Bank CI',
            'Banque Populaire de CI',
            'UBA CI (United Bank for Africa)',
            'Standard Chartered CI',
            'BGFI Bank CI',
            'Versus Bank',
            'GT Bank CI',
            'BMS CI',
            'Banque de l\'Union CI',
        ];

        foreach ($banks as $name) {
            Bank::updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
