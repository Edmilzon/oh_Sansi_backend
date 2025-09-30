<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('nivel')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ejemplo: Asignar área Matemáticas
        $areaMatematicas = DB::table('area')->where('nombre', 'Matemáticas')->first();
        $idAreaMatematicas = $areaMatematicas ? $areaMatematicas->id_area : null;

        $niveles = [
            ['nombre' => 'Inicial', 'descripcion' => 'Nivel inicial', 'orden' => 1, 'id_area' => $idAreaMatematicas],
            ['nombre' => 'Intermedio', 'descripcion' => 'Nivel intermedio', 'orden' => 2, 'id_area' => $idAreaMatematicas],
            ['nombre' => 'Avanzado', 'descripcion' => 'Nivel avanzado', 'orden' => 3, 'id_area' => $idAreaMatematicas],
        ];

        DB::table('nivel')->insert($niveles);
    }
}