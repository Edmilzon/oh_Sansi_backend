<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Area;
use App\Model\Olimpiada;

class AreasSeeder extends Seeder
{
    public function run(): void
    {
        $areasNombres = [
            'Matemáticas', 'Física', 'Química', 'Biología',
            'Informática', 'Robótica', 'Astronomía', 'Astrofísica'
        ];

        $this->command->info('Verificando áreas...');
        foreach ($areasNombres as $nombre) {
            Area::firstOrCreate(['nombre' => $nombre]);
        }

        $olimpiada = Olimpiada::latest('id_olimpiada')->first();
        if ($olimpiada) {
            $this->command->info("Asociando áreas a: {$olimpiada->nombre}");
            $areasIds = Area::pluck('id_area');
            $olimpiada->areas()->syncWithoutDetaching($areasIds);
        }

        $this->command->info('Áreas académicas listas.');
    }
}
