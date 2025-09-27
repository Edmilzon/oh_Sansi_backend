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

        $niveles = [
            ['nombre' => 'Inicial', 'descripcion' => 'Nivel inicial', 'orden' => 1],
            ['nombre' => 'Intermedio', 'descripcion' => 'Nivel intermedio', 'orden' => 2],
            ['nombre' => 'Avanzado', 'descripcion' => 'Nivel avanzado', 'orden' => 3],
        ];

        DB::table('nivel')->insert($niveles);
    }
}