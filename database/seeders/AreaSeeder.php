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
    ['nombre' => 'Biología', 'descripcion' => 'Olimpiadas Científicas de Biología', 'activo' => true],
    ['nombre' => 'Historia', 'descripcion' => 'Olimpiadas Científicas de Historia', 'activo' => true],
    ['nombre' => 'Geografía', 'descripcion' => 'Olimpiadas Científicas de Geografía', 'activo' => true],
    ['nombre' => 'Literatura', 'descripcion' => 'Olimpiadas Científicas de Literatura', 'activo' => true],
    ['nombre' => 'Inglés', 'descripcion' => 'Olimpiadas Científicas de Inglés', 'activo' => true],
    ['nombre' => 'Música', 'descripcion' => 'Olimpiadas Científicas de Música', 'activo' => true],
    ['nombre' => 'Educación Física', 'descripcion' => 'Olimpiadas Científicas de Educación Física', 'activo' => true],
    ['nombre' => 'Química Orgánica', 'descripcion' => 'Olimpiadas Científicas de Química Orgánica', 'activo' => true],
    ['nombre' => 'Física Aplicada', 'descripcion' => 'Olimpiadas Científicas de Física Aplicada', 'activo' => true],
    ['nombre' => 'Filosofía', 'descripcion' => 'Olimpiadas Científicas de Filosofía', 'activo' => true],
    ['nombre' => 'Economía', 'descripcion' => 'Olimpiadas Científicas de Economía', 'activo' => true],
    ['nombre' => 'Arte', 'descripcion' => 'Olimpiadas Científicas de Arte', 'activo' => true],
];


        DB::table('area')->insert($areas);
    }
}