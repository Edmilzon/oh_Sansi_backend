<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\Nivel;
use App\Models\AreaNivel;
use Illuminate\Support\Facades\DB;

class AreaNivelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AreaNivel::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $areas = Area::all();
        $niveles = Nivel::all();

        if ($areas->isEmpty() || $niveles->isEmpty()) {
            $this->command->warn('Por favor, ejecuta primero los seeders de Area y Nivel.');
            return;
        }

        foreach ($areas as $area) {
            foreach ($niveles as $nivel) {
                AreaNivel::create([
                    'id_area' => $area->id_area,
                    'id_nivel' => $nivel->id_nivel,
                    'activo' => true,
                ]);
            }
        }
    }
}