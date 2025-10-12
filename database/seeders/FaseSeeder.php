<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fase;
use Illuminate\Support\Facades\DB;

class FaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('fase')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

       $fase = [
    ['nombre' => 'Primera', 'orden' => 1, 'descripcion' => 'Fase inicial de la competencia', 'nota_minima_clasificacion' => null, 'cantidad_maxima_de_clasificados' => null],
    ['nombre' => 'Segunda', 'orden' => 2, 'descripcion' => 'Segunda etapa clasificatoria', 'nota_minima_clasificacion' => null, 'cantidad_maxima_de_clasificados' => null],
    ['nombre' => 'Tercera', 'orden' => 3, 'descripcion' => 'Tercera etapa clasificatoria', 'nota_minima_clasificacion' => null, 'cantidad_maxima_de_clasificados' => null],
    ['nombre' => 'Final', 'orden' => 4, 'descripcion' => 'Fase final de la competencia', 'nota_minima_clasificacion' => null, 'cantidad_maxima_de_clasificados' => null],

];
        DB::table('fase')->insert($fase);
    }
}