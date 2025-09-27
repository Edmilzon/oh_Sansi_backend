<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use Illuminate\Support\Facades\DB;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('area')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $areas = [
            ['nombre' => 'Matemáticas', 'descripcion' => 'Olimpiadas Científicas de Matemáticas', 'activo' => true],
            ['nombre' => 'Física', 'descripcion' => 'Olimpiadas Científicas de Física', 'activo' => true],
            ['nombre' => 'Química', 'descripcion' => 'Olimpiadas Científicas de Química', 'activo' => true],
            ['nombre' => 'Informática', 'descripcion' => 'Olimpiadas Científicas de Informática', 'activo' => true],
        ];

        DB::table('area')->insert($areas);
    }
}