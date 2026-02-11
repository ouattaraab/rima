<?php

namespace Database\Seeders;

use App\Models\Direction;
use Illuminate\Database\Seeder;

class DirectionSeeder extends Seeder
{
    public function run(): void
    {
        $directions = [
            ['code' => 'DT', 'name' => 'Direction Technique'],
            ['code' => 'DC', 'name' => 'Direction Commerciale'],
            ['code' => 'DF', 'name' => 'Direction Financiere'],
            ['code' => 'DRH', 'name' => 'Direction des Ressources Humaines'],
            ['code' => 'DL', 'name' => 'Direction Logistique'],
            ['code' => 'DSI', 'name' => 'Direction des Systemes d\'Information'],
            ['code' => 'DG', 'name' => 'Direction Generale'],
            ['code' => 'DAJ', 'name' => 'Direction des Affaires Juridiques'],
            ['code' => 'DPE', 'name' => 'Direction de la Planification et des Etudes'],
            ['code' => 'DCG', 'name' => 'Direction du Controle de Gestion'],
            ['code' => 'DAF', 'name' => 'Direction Administrative et Financiere'],
            ['code' => 'DE', 'name' => 'Direction de l\'Exploitation'],
        ];

        foreach ($directions as $d) {
            Direction::create($d);
        }
    }
}
