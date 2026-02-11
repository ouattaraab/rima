<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Blanc', 'Noir', 'Gris', 'Bleu', 'Rouge', 'Vert', 'Jaune', 'Beige', 'Marron', 'Autre'] as $name) {
            Color::updateOrCreate(['name' => $name]);
        }
    }
}
