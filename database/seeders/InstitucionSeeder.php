<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitucionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('institucion')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $instituciones = [
            ['nombre' => 'Colegio Don Bosco', 'tipo' => 'Privado', 'departamento' => 'Cochabamba', 'direccion' => 'Av. Oquendo'],
            ['nombre' => 'Colegio La Salle', 'tipo' => 'Privado', 'departamento' => 'Cochabamba', 'direccion' => 'Av. Ayacucho'],
            ['nombre' => 'Colegio San Agustín', 'tipo' => 'Privado', 'departamento' => 'Cochabamba', 'direccion' => 'Av. Heroínas'],
            ['nombre' => 'Unidad Educativa Abaroa', 'tipo' => 'Público', 'departamento' => 'Cochabamba', 'direccion' => 'Zona Sud'],
        ];

        DB::table('institucion')->insert($instituciones);
    }
}