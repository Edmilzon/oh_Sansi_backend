<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CodigoEncargado;
use Illuminate\Support\Facades\DB;

class CodigoEncargadoSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CodigoEncargado::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $matematicas_id = DB::table('area')->where('nombre', 'Matemáticas')->value('id_area');
        $fisica_id = DB::table('area')->where('nombre', 'Física')->value('id_area');
        $quimica_id = DB::table('area')->where('nombre', 'Química')->value('id_area');
        $biologia_id = DB::table('area')->where('nombre', 'Biología')->value('id_area');
        $historia_id = DB::table('area')->where('nombre', 'Historia')->value('id_area');
        $geografia_id = DB::table('area')->where('nombre', 'Geografía')->value('id_area');
        $literatura_id = DB::table('area')->where('nombre', 'Literatura')->value('id_area');
        $ingles_id = DB::table('area')->where('nombre', 'Inglés')->value('id_area');
        $informatica_id = DB::table('area')->where('nombre', 'Informática')->value('id_area');
        $musica_id = DB::table('area')->where('nombre', 'Música')->value('id_area');
        $educacion_fisica_id = DB::table('area')->where('nombre', 'Educación Física')->value('id_area');
        $quimica_org_id = DB::table('area')->where('nombre', 'Química Orgánica')->value('id_area');
        $fisica_app_id = DB::table('area')->where('nombre', 'Física Aplicada')->value('id_area');
        $filosofia_id = DB::table('area')->where('nombre', 'Filosofía')->value('id_area');
        $economia_id = DB::table('area')->where('nombre', 'Economía')->value('id_area');

        $codigos = [
            ['codigo' => 'MAT01', 'descripcion' => 'Código Matemáticas', 'id_area' => $matematicas_id],
            ['codigo' => 'FIS01', 'descripcion' => 'Código Física', 'id_area' => $fisica_id],
            ['codigo' => 'QUI01', 'descripcion' => 'Código Química', 'id_area' => $quimica_id],
            ['codigo' => 'BIO01', 'descripcion' => 'Código Biología', 'id_area' => $biologia_id],
            ['codigo' => 'HIS01', 'descripcion' => 'Código Historia', 'id_area' => $historia_id],
            ['codigo' => 'GEO01', 'descripcion' => 'Código Geografía', 'id_area' => $geografia_id],
            ['codigo' => 'LIT01', 'descripcion' => 'Código Literatura', 'id_area' => $literatura_id],
            ['codigo' => 'ENG01', 'descripcion' => 'Código Inglés', 'id_area' => $ingles_id],
            ['codigo' => 'INF01', 'descripcion' => 'Código Informática', 'id_area' => $informatica_id],
            ['codigo' => 'MUS01', 'descripcion' => 'Código Música', 'id_area' => $musica_id],
            ['codigo' => 'EF01', 'descripcion' => 'Código Educación Física', 'id_area' => $educacion_fisica_id],
            ['codigo' => 'QO01', 'descripcion' => 'Código Química Orgánica', 'id_area' => $quimica_org_id],
            ['codigo' => 'FA01', 'descripcion' => 'Código Física Aplicada', 'id_area' => $fisica_app_id],
            ['codigo' => 'FIL01', 'descripcion' => 'Código Filosofía', 'id_area' => $filosofia_id],
            ['codigo' => 'ECO01', 'descripcion' => 'Código Economía', 'id_area' => $economia_id],
        ];


        foreach ($codigos as $codigo) {
            CodigoEncargado::create($codigo);
        }
    }
}
