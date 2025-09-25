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

        $codigos = [
            ['codigo' => '1234', 'descripcion' => 'Código evaluador Matemáticas Nivel Básico', 'activo' => true],
            ['codigo' => '5678', 'descripcion' => 'Código evaluador Matemáticas Nivel Intermedio', 'activo' => true],
            ['codigo' => '9101', 'descripcion' => 'Código evaluador Matemáticas Nivel Avanzado', 'activo' => true],
            //  añadir más aquí
        ];

        foreach ($codigos as $codigo) {
            CodigoEvaluador::create($codigo);
        }
    }
}
