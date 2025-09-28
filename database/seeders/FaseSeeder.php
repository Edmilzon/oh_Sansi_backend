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
    ['nombre' => 'Primera', 'orden' => '1' , 'descripcion' => '', 'nota_minima_clasificacion' => '', 'cantidad_maxima_de_clasificados' => ''],
    ['nombre' => 'Segunda', 'orden' => '2' , 'descripcion' => '', 'nota_minima_clasificacion' => '', 'cantidad_maxima_de_clasificados' => ''],
    ['nombre' => 'Tercera', 'orden' => '3' ,'descripcion' => '', 'nota_minima_clasificacion' => '', 'cantidad_maxima_de_clasificados' => ''],
    ['nombre' => 'Final', 'orden' => '4' , 'descripcion' => '', 'nota_minima_clasificacion' => '', 'cantidad_maxima_de_clasificados' => ''],

];
        DB::table('fase')->insert($fase);
    }
}