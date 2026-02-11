<?php

namespace Database\Seeders;

use App\Models\Structure;
use Illuminate\Database\Seeder;

class StructureSeeder extends Seeder
{
    public function run(): void
    {
        $structures = [
            ['code' => 'DRA', 'name' => 'Direction Regionale Abidjan', 'region' => 'Abidjan'],
            ['code' => 'DRB', 'name' => 'Direction Regionale Bouake', 'region' => 'Bouake'],
            ['code' => 'DRY', 'name' => 'Direction Regionale Yamoussoukro', 'region' => 'Yamoussoukro'],
            ['code' => 'DRSP', 'name' => 'Direction Regionale San-Pedro', 'region' => 'San-Pedro'],
            ['code' => 'DRD', 'name' => 'Direction Regionale Daloa', 'region' => 'Daloa'],
            ['code' => 'DRK', 'name' => 'Direction Regionale Korhogo', 'region' => 'Korhogo'],
            ['code' => 'DRM', 'name' => 'Direction Regionale Man', 'region' => 'Man'],
            ['code' => 'DRAB', 'name' => 'Direction Regionale Abengourou', 'region' => 'Abengourou'],
            ['code' => 'DRG', 'name' => 'Direction Regionale Gagnoa', 'region' => 'Gagnoa'],
            ['code' => 'DG', 'name' => 'Direction Generale', 'region' => 'Abidjan'],
        ];

        foreach ($structures as $s) {
            Structure::create($s);
        }
    }
}
