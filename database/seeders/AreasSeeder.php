<?php

namespace Database\Seeders;

use App\Model\Area;
use Illuminate\Database\Seeder;

class AreasSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['nombre' => 'Matemáticas'],
            ['nombre' => 'Física'],
            ['nombre' => 'Química'],
            ['nombre' => 'Biología'],
            ['nombre' => 'Informática'],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }

        $this->command->info('Áreas creadas exitosamente');
    }
}
