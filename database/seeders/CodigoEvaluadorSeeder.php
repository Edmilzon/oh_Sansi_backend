<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CodigoEvaluador;
use Illuminate\Support\Facades\DB;

class CodigoEvaluadorSeeder extends Seeder
{

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CodigoEvaluador::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Busca los IDs de área y nivel
        $matematicas_id = DB::table('area')->where('nombre', 'Matemáticas')->value('id_area');
        $nivel_basico_id = DB::table('nivel')->where('nombre', 'Inicial')->value('id_nivel');
        $nivel_intermedio_id = DB::table('nivel')->where('nombre', 'Intermedio')->value('id_nivel');
        $nivel_avanzado_id = DB::table('nivel')->where('nombre', 'Avanzado')->value('id_nivel');

        $codigos = [
            [
                'codigo' => 'E123MAT1P',
                'descripcion' => 'Código evaluador Matemáticas Nivel Básico',
                'activo' => true,
                'id_area' => $matematicas_id,
                'id_nivel' => $nivel_basico_id,
            ],
            [
                'codigo' => 'E123MAT',
                'descripcion' => 'Código evaluador Matemáticas Nivel Intermedio',
                'activo' => true,
                'id_area' => $matematicas_id,
                'id_nivel' => $nivel_intermedio_id,
            ],
            [
                'codigo' => '9101',
                'descripcion' => 'Código evaluador Matemáticas Nivel Avanzado',
                'activo' => true,
                'id_area' => $matematicas_id,
                'id_nivel' => $nivel_avanzado_id,
            ],
        ];

        foreach ($codigos as $codigo) {
            CodigoEvaluador::create($codigo);
        }
    }
}
